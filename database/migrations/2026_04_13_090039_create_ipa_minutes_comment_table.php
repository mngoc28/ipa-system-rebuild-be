<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_minutes_comment', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('minutes_id');
            $table->unsignedBigInteger('version_id')->nullable();
            $table->unsignedBigInteger('commenter_user_id');
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->text('comment_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_minutes_comment');
    }
};
