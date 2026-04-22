<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to optimize notification and user lookups.
     */
    public function up(): void
    {
        // Optimize user lookups for mock identity resolution
        Schema::table('ipa_user', function (Blueprint $table) {
            $table->index('username');
            $table->index('email');
        });

        // Optimize notification retrieval and status updates
        Schema::table('ipa_notification_recipient', function (Blueprint $table) {
            $table->index('recipient_user_id');
            $table->index('notification_id');
            $table->index('read_at');
        });

        // Optimize sorting by creation date
        Schema::table('ipa_notification', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipa_notification', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('ipa_notification_recipient', function (Blueprint $table) {
            $table->dropIndex(['recipient_user_id']);
            $table->dropIndex(['notification_id']);
            $table->dropIndex(['read_at']);
        });

        Schema::table('ipa_user', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['email']);
        });
    }
};
