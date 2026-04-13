<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipa_delegation_checklist', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id');
            $table->string('item_name');
            $table->unsignedBigInteger('assignee_user_id')->nullable();
            $table->date('due_date')->nullable();
            $table->smallInteger('status')->default(0);
            $table->smallInteger('priority')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation_checklist');
    }
};
