<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_approval_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('approval_request_id');
            $table->smallInteger('old_status');
            $table->smallInteger('new_status');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_approval_history');
    }
};
