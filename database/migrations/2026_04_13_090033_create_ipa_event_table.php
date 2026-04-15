<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_event', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->smallInteger('event_type')->default(1);
            $table->smallInteger('status')->default(0);
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('organizer_user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_event');
    }
};
