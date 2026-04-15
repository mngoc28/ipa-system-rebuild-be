<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_event_external_participant', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->string('full_name');
            $table->string('organization_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_event_external_participant');
    }
};
