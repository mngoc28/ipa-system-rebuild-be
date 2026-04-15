<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_minutes_signature', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('minutes_id');
            $table->unsignedBigInteger('signer_user_id')->nullable();
            $table->string('signer_name');
            $table->string('signer_role')->nullable();
            $table->unsignedBigInteger('signature_file_id')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_minutes_signature');
    }
};
