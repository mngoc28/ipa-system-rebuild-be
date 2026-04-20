<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_delegation_comment', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id');
            $table->unsignedBigInteger('commenter_user_id');
            $table->text('comment_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation_comment');
    }
};
