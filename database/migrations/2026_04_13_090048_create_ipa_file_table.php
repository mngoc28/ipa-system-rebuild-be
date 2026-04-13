<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_file', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('file_name');
            $table->string('file_ext')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('size_bytes');
            $table->string('storage_key');
            $table->string('checksum')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedBigInteger('delegation_id')->nullable();
            $table->unsignedBigInteger('minutes_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_file');
    }
};
