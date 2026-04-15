<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_integration_endpoint', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('provider_code');
            $table->text('base_url')->nullable();
            $table->string('app_id')->nullable();
            $table->string('secret_ref')->nullable();
            $table->smallInteger('status')->default(1);
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_integration_endpoint');
    }
};
