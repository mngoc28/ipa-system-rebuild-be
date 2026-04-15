<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_report_definition', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('report_code');
            $table->string('report_name');
            $table->smallInteger('scope_type')->default(0);
            $table->unsignedBigInteger('owner_role_id')->nullable();
            $table->json('query_config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_report_definition');
    }
};
