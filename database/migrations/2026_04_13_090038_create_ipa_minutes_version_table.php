<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_minutes_version', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('minutes_id');
            $table->integer('version_no');
            $table->text('content_text')->nullable();
            $table->json('content_json')->nullable();
            $table->text('change_summary')->nullable();
            $table->unsignedBigInteger('edited_by');
            $table->timestamp('edited_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_minutes_version');
    }
};
