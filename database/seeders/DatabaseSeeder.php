<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetSeedTables();

        $this->call([
            IpaCountrySeeder::class,
            IpaMdSectorSeeder::class,
            IpaMdDelegationTypeSeeder::class,
            IpaMdPartnerStatusSeeder::class,
            IpaMdPrioritySeeder::class,
            IpaMdTaskStatusSeeder::class,
            IpaMdWorkflowStatusSeeder::class,
            IpaMdPipelineStageSeeder::class,
            IpaMdNotificationTypeSeeder::class,
            IpaOrgUnitSeeder::class,
            IpaUserSeeder::class,
            IpaRoleSeeder::class,
            IpaUserRoleSeeder::class,
            IpaRbacFakeDataSeeder::class,
            IpaPartnerSeeder::class,
            IpaPartnerContactSeeder::class,
            IpaPartnerInteractionSeeder::class,
            IpaLocationSeeder::class,
            IpaDelegationSeeder::class,
            IpaDelegationOutcomeSeeder::class,
            IpaEventSeeder::class,
            IpaTaskSeeder::class,
            IpaEventParticipantSeeder::class,
            IpaDelegationMemberSeeder::class,
            IpaUserUnitAssignmentSeeder::class,
            IpaSystemSettingSeeder::class,
            IpaAuditLogSeeder::class,
            IpaAuthSessionSeeder::class,
            IpaLoginAttemptSeeder::class,
            IpaTaskCommentSeeder::class,
            IpaLinkedChildSeeder::class,
        ]);
    }

    private function resetSeedTables(): void
    {
        Schema::disableForeignKeyConstraints();

        $driver = DB::getDriverName();
        $tables = match ($driver) {
            'mysql' => array_map(
                static fn (object $row): string => (string) (array_values((array) $row)[0] ?? ''),
                DB::select('SHOW TABLES'),
            ),
            'sqlite' => array_map(
                static fn (object $row): string => (string) $row->name,
                DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"),
            ),
            'pgsql' => array_map(
                static fn (object $row): string => (string) $row->tablename,
                DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"),
            ),
            'sqlsrv' => array_map(
                static fn (object $row): string => (string) $row->TABLE_NAME,
                DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"),
            ),
            default => [],
        };

        foreach ($tables as $tableName) {
            $tableName = trim($tableName);

            if ($tableName === null || $tableName === 'migrations') {
                continue;
            }

            DB::table($tableName)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }
}
