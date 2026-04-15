<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_task_status_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('task_id');
            $table->smallInteger('old_status');
            $table->smallInteger('new_status');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_task_status_history');
    }
};
