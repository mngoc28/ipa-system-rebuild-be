<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_event_reschedule_request', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('proposed_start_at');
            $table->timestamp('proposed_end_at');
            $table->text('reason')->nullable();
            $table->smallInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_event_reschedule_request');
    }
};
