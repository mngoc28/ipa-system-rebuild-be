<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $actorId = DB::table('ipa_user')->value('id');

        $logs = [
            [
                'action' => 'create_pipeline_project',
                'resource_type' => 'pipeline_project',
                'resource_id' => 53,
                'before_json' => null,
                'after_json' => json_encode(['project_code' => 'CITY-PIPE-2026-IT-01']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(5),
            ],
            [
                'action' => 'update_system_setting',
                'resource_type' => 'system_setting',
                'resource_id' => null,
                'before_json' => json_encode(['smtp_host' => 'smtp.old.example']),
                'after_json' => json_encode(['smtp_host' => 'smtp.ipa.danang.gov.vn']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(4),
            ],
            [
                'action' => 'approve_report_run',
                'resource_type' => 'report_run',
                'resource_id' => 55,
                'before_json' => json_encode(['status' => 'queued']),
                'after_json' => json_encode(['status' => 'done']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(3),
            ],
            [
                'action' => 'delete_file',
                'resource_type' => 'file',
                'resource_id' => 1,
                'before_json' => json_encode(['file_name' => 'obsolete.pdf']),
                'after_json' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(2),
            ],
            [
                'action' => 'login_success',
                'resource_type' => 'auth_session',
                'resource_id' => null,
                'before_json' => null,
                'after_json' => json_encode(['role' => 'director']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHour(),
            ],
        ];

        foreach ($logs as $log) {
            if (DB::table('ipa_audit_log')->where('action', $log['action'])->where('resource_type', $log['resource_type'])->exists()) {
                continue;
            }

            DB::table('ipa_audit_log')->insert([
                'actor_user_id' => $actorId,
                ...$log,
                'created_at' => $log['created_at'],
                'updated_at' => $log['created_at'],
            ]);
        }
    }
}
