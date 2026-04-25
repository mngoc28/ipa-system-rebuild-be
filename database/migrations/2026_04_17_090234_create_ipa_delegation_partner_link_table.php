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
        // Drop the old single partner_id column
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('ipa_delegation', function (Blueprint $table): void {
                $table->dropColumn('partner_id');
            });
        }

        // Create pivot table
        Schema::create('ipa_delegation_partner_link', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegation_id');
            $table->unsignedBigInteger('partner_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation_partner_link');

        Schema::table('ipa_delegation', function (Blueprint $table): void {
            $table->unsignedBigInteger('partner_id')->nullable()->after('country_id');
        });
    }
};
