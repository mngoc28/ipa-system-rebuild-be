<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_data_change_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('table_name');
            $table->unsignedBigInteger('row_id');
            $table->smallInteger('operation');
            $table->json('diff_json');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_data_change_history');
    }
};
