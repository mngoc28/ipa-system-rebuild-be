<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_task_attachment', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('file_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_task_attachment');
    }
};
