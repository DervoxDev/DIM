<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_sources', function (Blueprint $table) {
            $table->boolean('is_default_general')->default(false);
            $table->boolean('is_default_sales')->default(false);
            $table->boolean('is_default_purchases')->default(false);
            $table->boolean('is_default_payments')->default(false);
        });
    }

    public function down()
    {
        Schema::table('cash_sources', function (Blueprint $table) {
            $table->dropColumn([
                'is_default_general',
                'is_default_sales',
                'is_default_purchases',
                'is_default_payments'
            ]);
        });
    }
};
