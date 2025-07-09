<?php

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
        Schema::table('users', function (Blueprint $table) {
            // Check if column exists and modify it to store ISO3 country codes
            if (Schema::hasColumn('users', 'country_id')) {
                $table->string('country_id', 3)->nullable()->change();
            } else {
                $table->string('country_id', 3)->nullable()->after('team_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_id')) {
                $table->dropColumn('country_id');
            }
        });
    }
};
