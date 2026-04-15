<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_outbox_event', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('event_type');
            $table->json('payload_json');
            $table->smallInteger('status')->default(0);
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_outbox_event');
    }
};
