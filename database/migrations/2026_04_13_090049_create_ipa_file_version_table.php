<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_file_version', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('file_id');
            $table->integer('version_no');
            $table->string('storage_key');
            $table->bigInteger('size_bytes');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_file_version');
    }
};
