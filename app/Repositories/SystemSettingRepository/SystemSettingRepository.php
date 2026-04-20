<?php

declare(strict_types=1);

namespace App\Repositories\SystemSettingRepository;

use App\Models\SystemSetting;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class SystemSettingRepository extends BaseRepository implements SystemSettingRepositoryInterface
{
    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return SystemSetting::class;
    }

    /**
     * Get all currently supported system setting groups from configuration.
     *
     * @return array
     */
    public function getAllowedGroups(): array
    {
        return array_keys(config('system_settings.groups', []));
    }

    /**
     * Retrieve all setting items, filtered by group, and merged with their definitions and stored values.
     *
     * @param array|null $groups
     * @return array
     */
    public function getAllByGroups(?array $groups = null): array
    {
        $definitions = config('system_settings.groups', []);
        $allowedGroups = $groups !== null && $groups !== []
            ? array_values(array_intersect($groups, $this->getAllowedGroups()))
            : $this->getAllowedGroups();

        $rows = DB::table('ipa_system_setting')
            ->whereIn('setting_group', $allowedGroups)
            ->orderBy('setting_group')
            ->orderBy('setting_key')
            ->get()
            ->keyBy('setting_key');

        $items = [];

        foreach ($allowedGroups as $group) {
            foreach ($definitions[$group] ?? [] as $definition) {
                $record = $rows->get($definition['key']);
                $items[] = $this->normalizeDefinition($group, $definition, $record);
            }
        }

        return [
            'items' => $items,
        ];
    }

    /**
     * Batch save multiple configuration items, handling encryption for secret fields.
     *
     * @param array $items
     * @param int|null $updatedBy
     * @return int
     */
    public function saveItems(array $items, ?int $updatedBy = null): int
    {
        $definitions = $this->definitionMap();
        $updatedCount = 0;

        DB::transaction(function () use ($items, $definitions, $updatedBy, &$updatedCount): void {
            foreach ($items as $item) {
                $key = trim((string) Arr::get($item, 'key', ''));
                if ($key === '' || ! isset($definitions[$key])) {
                    continue;
                }

                $definition = $definitions[$key];
                $value = Arr::get($item, 'value');
                $value = is_string($value) ? trim($value) : (string) $value;

                if (($definition['is_secret'] ?? false) === true && $value === '') {
                    continue;
                }

                $this->persistDefinition($definition, $value, $updatedBy);
                $updatedCount++;
            }
        });

        return $updatedCount;
    }

    /**
     * Get the plaintext resolved value for a setting, handling decryption if the setting is a secret.
     *
     * @param string $key
     * @return string|null
     */
    public function getResolvedValue(string $key): ?string
    {
        $definition = $this->definitionMap()[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        $record = DB::table('ipa_system_setting')->where('setting_key', $key)->first();

        if (! $record) {
            return (string) ($definition['default_value'] ?? '');
        }

        if ((bool) $definition['is_secret'] === true) {
            return $this->decryptSecret($record->encrypted_value ?? null);
        }

        return (string) ($record->setting_value ?? $definition['default_value'] ?? '');
    }

    /**
     * Check if a setting has a non-empty value (either plaintext or encrypted).
     *
     * @param string $key
     * @return bool
     */
    public function hasValue(string $key): bool
    {
        $record = DB::table('ipa_system_setting')->where('setting_key', $key)->first();

        if (! $record) {
            return false;
        }

        $hasValue = ($record->setting_value !== null && $record->setting_value !== '');
        $hasSecret = ($record->encrypted_value !== null && $record->encrypted_value !== '');

        return $hasValue || $hasSecret;
    }

    /**
     * Flatten all group-based definitions into a single key-to-definition map.
     *
     * @return array
     */
    private function definitionMap(): array
    {
        $map = [];

        foreach (config('system_settings.groups', []) as $group => $definitions) {
            foreach ($definitions as $definition) {
                $map[$definition['key']] = $definition + ['group' => $group];
            }
        }

        return $map;
    }

    /**
     * Standardize a setting definition and its optional database record into a response array.
     * Handles masking for secret values.
     *
     * @param string $group
     * @param array $definition
     * @param mixed $record
     * @return array
     */
    private function normalizeDefinition(string $group, array $definition, mixed $record): array
    {
        $isSecret = (bool) ($definition['is_secret'] ?? false);
        $hasValue = $record !== null && (
            (($record->setting_value ?? null) !== null && ($record->setting_value ?? null) !== '') ||
            (($record->encrypted_value ?? null) !== null && ($record->encrypted_value ?? null) !== '')
        );

        return [
            'key' => (string) $definition['key'],
            'group' => $group,
            'label' => (string) ($definition['label'] ?? Str::headline(str_replace('_', ' ', (string) $definition['key']))),
            'value' => $isSecret ? null : (string) ($record->setting_value ?? $definition['default_value'] ?? ''),
            'maskedValue' => $isSecret ? ($hasValue ? '********' : '') : null,
            'is_secret' => $isSecret,
            'has_value' => $hasValue,
            'options' => $definition['options'] ?? [],
            'updated_at' => $record?->updated_at instanceof Carbon
                ? $record->updated_at->toIso8601String()
                : ($record?->updated_at ? Carbon::parse((string) $record->updated_at)->toIso8601String() : null),
        ];
    }

    /**
     * Insert or update a setting record in the database, handling data encryption for secrets.
     *
     * @param array $definition
     * @param string $value
     * @param int|null $updatedBy
     * @return void
     */
    private function persistDefinition(array $definition, string $value, ?int $updatedBy = null): void
    {
        $key = (string) $definition['key'];
        $group = (string) $definition['group'];
        $isSecret = (bool) ($definition['is_secret'] ?? false);
        $now = now();

        $payload = [
            'setting_group' => $group,
            'setting_key' => $key,
            'setting_value' => $isSecret ? null : $value,
            'encrypted_value' => $isSecret ? $this->encryptSecret($value) : null,
            'is_secret' => $isSecret,
            'updated_by' => $updatedBy,
            'updated_at' => $now,
        ];

        $existing = DB::table('ipa_system_setting')->where('setting_key', $key)->first();

        if ($existing) {
            DB::table('ipa_system_setting')->where('setting_key', $key)->update($payload);

            return;
        }

        DB::table('ipa_system_setting')->insert($payload + [
            'created_at' => $now,
        ]);
    }

    /**
     * Encrypt a sensitive setting value using the system's encryption key.
     *
     * @param string $value
     * @return string
     */
    private function encryptSecret(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt a protected setting value, with a fallback to the raw value if decryption fails.
     *
     * @param string|null $value
     * @return string|null
     */
    private function decryptSecret(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
}
