<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('product_barcodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->string('barcode')->unique();
                $table->timestamps();
            });

            // Remove barcode from products table
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('barcode');
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('product_barcodes');

            Schema::table('products', function (Blueprint $table) {
                $table->string('barcode')->nullable();
            });
        }
    };
