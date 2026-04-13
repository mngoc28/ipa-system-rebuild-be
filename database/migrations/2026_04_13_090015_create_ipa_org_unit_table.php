<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_org_unit', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('unit_code');
            $table->string('unit_name');
            $table->string('unit_type');
            $table->unsignedBigInteger('parent_unit_id')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_org_unit');
    }
};
