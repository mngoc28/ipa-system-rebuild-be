<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_pipeline_stage_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pipeline_project_id');
            $table->unsignedBigInteger('old_stage_id');
            $table->unsignedBigInteger('new_stage_id');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_pipeline_stage_history');
    }
};
