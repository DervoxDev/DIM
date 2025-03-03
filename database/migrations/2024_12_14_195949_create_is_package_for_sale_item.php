<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->boolean('is_package')->default(false);
            $table->foreignId('package_id')->nullable()->constrained('product_packages')->nullOnDelete();
            $table->integer('total_pieces')->default(0);
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('is_package');
            $table->dropColumn('package_id');
            $table->dropColumn('total_pieces');
        });
    }
};
