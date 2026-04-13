<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_minutes_approval', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('minutes_id');
            $table->unsignedBigInteger('approver_user_id');
            $table->smallInteger('decision');
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_minutes_approval');
    }
};
