<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_folder', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_folder_id')->nullable();
            $table->string('folder_name');
            $table->unsignedBigInteger('owner_user_id');
            $table->smallInteger('scope_type')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_folder');
    }
};
