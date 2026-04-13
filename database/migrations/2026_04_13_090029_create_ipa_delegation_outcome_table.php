<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_delegation_outcome', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->text('summary')->nullable();
            $table->text('next_steps')->nullable();
            $table->timestamp('report_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation_outcome');
    }
};
