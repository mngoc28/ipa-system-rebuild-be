<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_message_template', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('template_code');
            $table->smallInteger('channel_type')->default(0);
            $table->string('language_code')->default('vi');
            $table->text('subject_template')->nullable();
            $table->text('body_template');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_message_template');
    }
};
