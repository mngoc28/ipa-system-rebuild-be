<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_approval_request', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('request_type');
            $table->string('ref_table');
            $table->unsignedBigInteger('ref_id');
            $table->unsignedBigInteger('requester_user_id');
            $table->integer('current_step')->default(1);
            $table->smallInteger('priority')->default(1);
            $table->timestamp('due_at')->nullable();
            $table->smallInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_approval_request');
    }
};
