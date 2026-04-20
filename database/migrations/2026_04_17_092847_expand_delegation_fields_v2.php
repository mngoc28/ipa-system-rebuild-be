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
        // Add fields to ipa_delegation
        Schema::table('ipa_delegation', function (Blueprint $table) {
            if (!Schema::hasColumn('ipa_delegation', 'investment_potential')) {
                $table->decimal('investment_potential', 15, 2)->nullable()->after('description');
            }
        });

        // Add fields to ipa_delegation_member
        Schema::table('ipa_delegation_member', function (Blueprint $table) {
            if (!Schema::hasColumn('ipa_delegation_member', 'gender')) {
                $table->string('gender')->nullable()->after('organization_name');
            }
            if (!Schema::hasColumn('ipa_delegation_member', 'identity_number')) {
                $table->string('identity_number')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('ipa_delegation_member', 'is_vip')) {
                $table->boolean('is_vip')->default(false)->after('member_type');
            }
        });

        // Add fields to ipa_event (Schedule)
        Schema::table('ipa_event', function (Blueprint $table) {
            // location_id usually exists in base table, check first
            if (!Schema::hasColumn('ipa_event', 'staff_id')) {
                $table->unsignedBigInteger('staff_id')->nullable()->after('location_id');
            }
            if (!Schema::hasColumn('ipa_event', 'logistics_note')) {
                $table->text('logistics_note')->nullable()->after('staff_id');
            }
        });

        // Create sectors link table
        if (!Schema::hasTable('ipa_delegation_sector_link')) {
            Schema::create('ipa_delegation_sector_link', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('delegation_id');
                $table->unsignedBigInteger('sector_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ipa_delegation_sector_link');

        Schema::table('ipa_event', function (Blueprint $table) {
            $table->dropColumn(['staff_id', 'logistics_note']);
        });

        Schema::table('ipa_delegation_member', function (Blueprint $table) {
            $table->dropColumn(['gender', 'identity_number', 'is_vip']);
        });

        Schema::table('ipa_delegation', function (Blueprint $table) {
            $table->dropColumn('investment_potential');
        });
    }
};
