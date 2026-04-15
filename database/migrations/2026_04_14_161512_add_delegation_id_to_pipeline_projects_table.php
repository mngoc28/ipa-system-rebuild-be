<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ipa_pipeline_project', function (Blueprint $table) {
            $table->unsignedBigInteger('delegation_id')->nullable()->after('sector_id');
            $table->foreign('delegation_id')->references('id')->on('ipa_delegation')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ipa_pipeline_project', function (Blueprint $table) {
            $table->dropForeign(['delegation_id']);
            $table->dropColumn('delegation_id');
        });
    }
};
