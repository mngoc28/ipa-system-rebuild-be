<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_partner_project', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('delegation_id')->nullable();
            $table->string('project_name');
            $table->unsignedBigInteger('stage_id');
            $table->decimal('estimated_value', 18, 2)->nullable();
            $table->decimal('success_probability', 5, 2)->nullable();
            $table->smallInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_partner_project');
    }
};
