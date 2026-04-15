<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_partner_interaction', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id');
            $table->smallInteger('interaction_type')->default(0);
            $table->timestamp('interaction_at');
            $table->unsignedBigInteger('owner_user_id');
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_partner_interaction');
    }
};
