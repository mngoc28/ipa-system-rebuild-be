<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaDataExpansionSeeder extends Seeder
{
    private const TARGET_COUNT = 50;

    public function run(): void
    {
        $dbName = DB::getDatabaseName();
        $tableRows = DB::select(
            'SELECT table_name AS tbl FROM information_schema.tables WHERE table_schema = ? AND table_name LIKE ?',
            [$dbName, 'ipa_%'],
        );

        foreach ($tableRows as $row) {
            $table = $row->tbl ?? null;
            if (!$table) {
                continue;
            }

            if (!$this->isMainTable($table)) {
                continue;
            }

            $currentCount = DB::table($table)->count();
            if ($currentCount === 0 || $currentCount >= self::TARGET_COUNT) {
                continue;
            }

            $template = (array) DB::table($table)->first();
            unset($template['id']);

            for ($i = $currentCount + 1; $i <= self::TARGET_COUNT; $i++) {
                $payload = $this->mutateRow($template, $table, $i);
                DB::table($table)->insert($payload);
            }
        }
    }

    private function isMainTable(string $table): bool
    {
        return str_starts_with($table, 'ipa_') && !str_starts_with($table, 'ipa_md_');
    }

    /**
     * @param array<string, mixed> $template
     * @return array<string, mixed>
     */
    private function mutateRow(array $template, string $table, int $index): array
    {
        $row = $template;

        foreach ($row as $column => $value) {
            if ($column === 'id') {
                unset($row[$column]);
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (in_array($column, ['created_at', 'updated_at', 'changed_at', 'edited_at', 'occurred_at', 'started_at', 'finished_at', 'attempted_at', 'interaction_at', 'invited_at', 'decided_at', 'signed_at', 'issued_at', 'expires_at', 'last_check_at', 'check_time', 'due_at', 'report_updated_at', 'revoked_at', 'proposed_start_at', 'proposed_end_at'], true)) {
                $row[$column] = now();
                continue;
            }
               
               if (str_ends_with($column, '_at') && is_string($value)) {
                   $row[$column] = now()->toDateTimeString();
                   continue;
               }

            if (str_contains($column, 'date') && is_string($value)) {
                $row[$column] = now()->toDateString();
                continue;
            }

            if (str_contains($column, 'json') && is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $decoded['_seed_index'] = $index;
                    $row[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                }
                continue;
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if (($trimmed !== '') && (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '['))) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $decoded['_seed_index'] = $index;
                        $row[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                        continue;
                    }
                }
            }

            if (is_string($value) && is_numeric($value)) {
                continue;
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed) === 1) {
                    $row[$column] = now()->toDateString();
                    continue;
                }
                if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $trimmed) === 1) {
                    $row[$column] = now()->toDateTimeString();
                    continue;
                }
            }

            if (is_string($value)) {
                if (str_contains($column, 'email')) {
                    $row[$column] = "seed{$index}_{$table}@example.com";
                } elseif (str_contains($column, 'code') || str_contains($column, 'name') || str_contains($column, 'title') || str_contains($column, 'reason') || str_contains($column, 'summary')) {
                    $row[$column] = "{$value}_{$index}";
                } else {
                    $row[$column] = "{$value}_{$index}";
                }
                continue;
            }

            if (is_int($value) || is_float($value)) {
                if (str_ends_with($column, '_id') || $column === 'id') {
                    continue;
                }
                $row[$column] = (is_float($value) ? (float) $value : (int) $value) + $index;
            }
        }

        return $row;
    }
}
