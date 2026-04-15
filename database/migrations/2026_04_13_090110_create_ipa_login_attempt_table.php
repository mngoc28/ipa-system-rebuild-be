<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_login_attempt', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('username_or_email');
            $table->string('ip_address')->nullable();
            $table->boolean('is_success')->default(false);
            $table->string('reason')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_login_attempt');
    }
};
