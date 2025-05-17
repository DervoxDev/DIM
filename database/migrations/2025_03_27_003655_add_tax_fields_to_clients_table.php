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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('if_number')->nullable()->after('tax_number');
            $table->string('rc_number')->nullable()->after('if_number');
            $table->string('cnss_number')->nullable()->after('rc_number');
            $table->string('tp_number')->nullable()->after('cnss_number');
            $table->string('nis_number')->nullable()->after('tp_number');
            $table->string('nif_number')->nullable()->after('nis_number');
            $table->string('ai_number')->nullable()->after('nif_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'if_number',
                'rc_number',
                'cnss_number',
                'tp_number',
                'nis_number',
                'nif_number',
                'ai_number',
            ]);
        });
    }
};
