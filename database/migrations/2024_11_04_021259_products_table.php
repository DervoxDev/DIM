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
            // First create an enum for product units
            Schema::create('product_units', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();  // e.g., 'liter', 'kilogram', 'unit', 'piece'
                $table->string('abbreviation')->unique();  // e.g., 'L', 'kg', 'unit', 'pc'
                $table->timestamps();
            });

            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('reference')->unique()->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('price');  // Required, changed to integer
                $table->integer('purchase_price')->nullable()->default(0);  // Required, new field
                $table->date('expired_date')->nullable();
                $table->integer('quantity')->nullable()->default(0);
                $table->foreignId('product_unit_id')->nullable()->constrained();
                $table->string('sku')->unique()->nullable();  // Stock Keeping Unit
                $table->string('barcode')->nullable();
                $table->string('status')->nullable()->default('active');  // active, discontinued, out_of_stock
                $table->integer('min_stock_level')->nullable()->default(0);  // for inventory alerts
                $table->integer('max_stock_level')->nullable()->default(0);
                $table->integer('reorder_point')->nullable()->default(0);  // when to reorder
                $table->string('location')->nullable();  // storage location
                $table->timestamps();
                $table->softDeletes();  // add soft deletes

                // Add indexes for commonly queried fields
                $table->index('reference');
                $table->index('name');
                $table->index('status');
                $table->index('expired_date');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('products');
            Schema::dropIfExists('product_units');
        }
    };
