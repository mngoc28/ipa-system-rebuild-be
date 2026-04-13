<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_approval_step', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('approver_user_id');
            $table->integer('step_order');
            $table->smallInteger('decision')->default(0);
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_approval_step');
    }
};
