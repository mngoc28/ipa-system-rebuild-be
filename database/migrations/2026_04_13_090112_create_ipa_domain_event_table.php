<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_domain_event', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('event_name');
            $table->string('aggregate_type');
            $table->unsignedBigInteger('aggregate_id');
            $table->json('payload_json');
            $table->timestamp('occurred_at');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_domain_event');
    }
};
