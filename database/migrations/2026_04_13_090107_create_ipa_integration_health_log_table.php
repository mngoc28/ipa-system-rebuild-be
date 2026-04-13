<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_integration_health_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('integration_id');
            $table->timestamp('check_time');
            $table->smallInteger('status');
            $table->integer('latency_ms')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_integration_health_log');
    }
};
