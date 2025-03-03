<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_source_id')->constrained()->cascadeOnDelete();
            $table->morphs('transactionable'); // For purchase or sale reference
            $table->decimal('amount', 12, 2);
            $table->string('type'); // deposit, withdrawal, transfer, purchase_payment, sale_payment
            $table->string('reference_number')->nullable();
            $table->foreignId('transfer_destination_id')->nullable()->constrained('cash_sources');
            $table->text('description')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_transactions');
    }
};