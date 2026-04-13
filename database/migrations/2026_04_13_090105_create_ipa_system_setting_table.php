<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_system_setting', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('setting_key');
            $table->string('setting_group');
            $table->text('setting_value')->nullable();
            $table->text('encrypted_value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_system_setting');
    }
};
