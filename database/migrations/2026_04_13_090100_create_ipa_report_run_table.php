<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_report_run', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_definition_id');
            $table->unsignedBigInteger('run_by');
            $table->json('params_json')->nullable();
            $table->unsignedBigInteger('output_file_id')->nullable();
            $table->smallInteger('status')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_report_run');
    }
};
