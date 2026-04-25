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
        Schema::table('ipa_delegation', function (Blueprint $table) {
            $table->index('host_unit_id');
            $table->index('owner_user_id');
            $table->index('country_id');
            $table->index(['status', 'deleted_at']);
            $table->index('direction');
            $table->index('priority');
            $table->index('deleted_at');
        });

        Schema::table('ipa_user', function (Blueprint $table) {
            $table->index('primary_unit_id');
            $table->index('status');
        });

        Schema::table('ipa_auth_session', function (Blueprint $table) {
            $table->index('refresh_token_hash');
            $table->index('user_id');
            $table->index(['revoked_at', 'expires_at']);
        });

        Schema::table('ipa_task_assignee', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('task_id');
        });

        Schema::table('ipa_task', function (Blueprint $table) {
            $table->index('is_overdue_cache');
        });

        Schema::table('ipa_approval_request', function (Blueprint $table) {
            $table->index('status');
            $table->index('request_type');
            $table->index('requester_user_id');
        });

        Schema::table('ipa_kpi_snapshot', function (Blueprint $table) {
            $table->index('metric_id');
            $table->index('snapshot_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipa_delegation', function (Blueprint $table) {
            $table->dropIndex(['host_unit_id']);
            $table->dropIndex(['owner_user_id']);
            $table->dropIndex(['country_id']);
            $table->dropIndex(['status', 'deleted_at']);
            $table->dropIndex(['direction']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('ipa_user', function (Blueprint $table) {
            $table->dropIndex(['primary_unit_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('ipa_auth_session', function (Blueprint $table) {
            $table->dropIndex(['refresh_token_hash']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['revoked_at', 'expires_at']);
        });

        Schema::table('ipa_task_assignee', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['task_id']);
        });

        Schema::table('ipa_task', function (Blueprint $table) {
            $table->dropIndex(['is_overdue_cache']);
        });

        Schema::table('ipa_approval_request', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['request_type']);
            $table->dropIndex(['requester_user_id']);
        });

        Schema::table('ipa_kpi_snapshot', function (Blueprint $table) {
            $table->dropIndex(['metric_id']);
            $table->dropIndex(['snapshot_date']);
        });
    }
};
