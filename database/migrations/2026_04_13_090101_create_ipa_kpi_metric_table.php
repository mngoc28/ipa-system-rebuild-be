<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_kpi_metric', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('metric_code');
            $table->string('metric_name');
            $table->string('unit');
            $table->smallInteger('scope_type')->default(0);
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_kpi_metric');
    }
};
