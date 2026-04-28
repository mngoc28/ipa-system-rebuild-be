<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ipa_delegation_member', function (Blueprint $table) {
            $table->index('delegation_id');
        });

        Schema::table('ipa_event', function (Blueprint $table) {
            $table->index('delegation_id');
            // organizer_user_id might already be indexed if it's a FK in some drivers, but good to be explicit
            $table->index('organizer_user_id');
        });

        Schema::table('ipa_delegation_checklist', function (Blueprint $table) {
            $table->index('delegation_id');
        });

        Schema::table('ipa_delegation_outcome', function (Blueprint $table) {
            $table->index('delegation_id');
        });

        Schema::table('ipa_delegation_contact', function (Blueprint $table) {
            $table->index('delegation_id');
        });

        Schema::table('ipa_delegation_partner_link', function (Blueprint $table) {
            $table->index('delegation_id');
            $table->index('partner_id');
        });

        Schema::table('ipa_delegation_sector_link', function (Blueprint $table) {
            $table->index('delegation_id');
            $table->index('sector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipa_delegation_sector_link', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
            $table->dropIndex(['sector_id']);
        });

        Schema::table('ipa_delegation_partner_link', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
            $table->dropIndex(['partner_id']);
        });

        Schema::table('ipa_delegation_contact', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
        });

        Schema::table('ipa_delegation_outcome', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
        });

        Schema::table('ipa_delegation_checklist', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
        });

        Schema::table('ipa_event', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
            $table->dropIndex(['organizer_user_id']);
        });

        Schema::table('ipa_delegation_member', function (Blueprint $table) {
            $table->dropIndex(['delegation_id']);
        });
    }
};
