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
    public function getModel(): string
    {
        return SystemSetting::class;
    }

    public function getAllowedGroups(): array
    {
        return array_keys(config('system_settings.groups', []));
    }

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

    private function encryptSecret(string $value): string
    {
        return Crypt::encryptString($value);
    }

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
