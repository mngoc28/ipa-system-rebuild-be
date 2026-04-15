<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaTaskAttachmentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_task_attachment')->exists()) {
            return;
        }

        DB::table('ipa_task_attachment')->insert([
                'task_id' => DB::table('ipa_task')->value('id'),
                'file_id' => DB::table('ipa_file')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
