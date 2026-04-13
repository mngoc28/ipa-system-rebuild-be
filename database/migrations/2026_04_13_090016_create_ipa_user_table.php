<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_user', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('email');
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('avatar_url')->nullable();
            $table->smallInteger('status')->default(1);
            $table->unsignedBigInteger('primary_unit_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_user');
    }
};
