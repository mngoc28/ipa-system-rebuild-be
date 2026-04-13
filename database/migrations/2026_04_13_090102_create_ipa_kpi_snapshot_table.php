<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_kpi_snapshot', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('metric_id');
            $table->date('snapshot_date');
            $table->unsignedBigInteger('org_unit_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->decimal('value_numeric', 20, 4)->nullable();
            $table->string('value_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_kpi_snapshot');
    }
};
