<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_partner_score_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id');
            $table->decimal('old_score', 3, 2);
            $table->decimal('new_score', 3, 2);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_partner_score_history');
    }
};
