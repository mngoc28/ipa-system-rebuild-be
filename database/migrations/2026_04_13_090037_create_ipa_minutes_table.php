<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_minutes', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('title');
            $table->integer('current_version_no')->default(1);
            $table->smallInteger('status')->default(0);
            $table->unsignedBigInteger('owner_user_id');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_minutes');
    }
};
