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
        Schema::table('ipa_user_role', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('role_id');
        });

        Schema::table('ipa_role_permission', function (Blueprint $table) {
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipa_role_permission', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropIndex(['permission_id']);
        });

        Schema::table('ipa_user_role', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['role_id']);
        });
    }
};
