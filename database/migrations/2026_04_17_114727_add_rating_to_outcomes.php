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
    public function up()
    {
        Schema::table('ipa_delegation_outcome', function (Blueprint $table) {
            if (!Schema::hasColumn('ipa_delegation_outcome', 'rating')) {
                $table->integer('rating')->default(0)->after('progress_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipa_delegation_outcome', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
