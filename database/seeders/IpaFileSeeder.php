<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFileSeeder extends Seeder
{
    public function run(): void
    {
        $folderId = DB::table('ipa_folder')->value('id');
        $uploadedBy = DB::table('ipa_user')->value('id');

        $files = [
            [
                'file_name' => 'BaoCao_TongHop_QuyI_2026.pdf',
                'file_ext' => 'pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 4210688,
                'storage_key' => 'reports/city/BaoCao_TongHop_QuyI_2026.pdf',
                'checksum' => 'report-city-q1-2026',
            ],
            [
                'file_name' => 'BaoCao_DongVon_FDI_2026.xlsx',
                'file_ext' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size_bytes' => 1810432,
                'storage_key' => 'reports/city/BaoCao_DongVon_FDI_2026.xlsx',
                'checksum' => 'report-city-fdi-2026',
            ],
            [
                'file_name' => 'BaoCao_DuBao_PCI_2026.pptx',
                'file_ext' => 'pptx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'size_bytes' => 12943360,
                'storage_key' => 'reports/city/BaoCao_DuBao_PCI_2026.pptx',
                'checksum' => 'report-city-pci-2026',
            ],
        ];

        foreach ($files as $file) {
            if (DB::table('ipa_file')->where('file_name', $file['file_name'])->exists()) {
                continue;
            }

            DB::table('ipa_file')->insert([
                'folder_id' => $folderId,
                ...$file,
                'uploaded_by' => $uploadedBy,
                'delegation_id' => null,
                'minutes_id' => null,
                'task_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
