<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_file_access_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('user_id');
            $table->smallInteger('action')->default(0);
            $table->string('ip_address')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_file_access_log');
    }
};
