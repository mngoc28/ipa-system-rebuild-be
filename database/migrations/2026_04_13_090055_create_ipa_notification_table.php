<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_notification', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notification_type_id');
            $table->string('title');
            $table->text('body');
            $table->string('ref_table')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->smallInteger('severity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_notification');
    }
};
