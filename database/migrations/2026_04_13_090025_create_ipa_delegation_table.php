<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_delegation', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
            $table->smallInteger('direction')->default(1);
            $table->smallInteger('status')->default(0);
            $table->smallInteger('priority')->default(1);
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('host_unit_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('participant_count')->default(0);
            $table->text('objective')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation');
    }
};
