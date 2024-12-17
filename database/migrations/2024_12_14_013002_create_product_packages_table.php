<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade'); 
            $table->string('name');  // e.g., "Box", "Pack", "Carton"
            $table->integer('pieces_per_package');  // e.g., 8 pieces per box
            $table->integer('purchase_price');  // price for the whole package
            $table->integer('selling_price');   // selling price for the package
            $table->string('barcode')->nullable();
            $table->timestamps();
            
            // Add index for faster queries
            $table->index(['product_id', 'team_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_packages');
    }
};
