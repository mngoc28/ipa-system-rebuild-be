<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_task', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('minutes_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->smallInteger('status')->default(0);
            $table->smallInteger('priority')->default(1);
            $table->timestamp('due_at')->nullable();
            $table->boolean('is_overdue_cache')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_task');
    }
};
