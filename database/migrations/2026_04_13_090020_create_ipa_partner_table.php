<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_partner', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('partner_code');
            $table->string('partner_name');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('sector_id');
            $table->smallInteger('status')->default(0);
            $table->decimal('score', 3, 2)->nullable();
            $table->text('website')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_partner');
    }
};
