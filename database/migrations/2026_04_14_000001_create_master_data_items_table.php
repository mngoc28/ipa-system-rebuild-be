<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('domain', 100)->index();
            $table->string('code', 100);
            $table->string('name_vi');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['domain', 'code']);
        });

        $now = now();
        $seedRows = [];

        foreach (config('master_data.domains', []) as $domain => $items) {
            foreach ($items as $item) {
                $seedRows[] = [
                    'id' => $item['id'] ?? (string) Str::uuid(),
                    'domain' => $domain,
                    'code' => $item['code'],
                    'name_vi' => $item['name_vi'],
                    'name_en' => $item['name_en'] ?? null,
                    'sort_order' => $item['sort_order'] ?? 0,
                    'is_active' => $item['is_active'] ?? true,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }
        }

        if ($seedRows !== []) {
            DB::table('master_data_items')->insert($seedRows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_items');
    }
};
