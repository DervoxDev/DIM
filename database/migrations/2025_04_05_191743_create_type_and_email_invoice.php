<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndEmailSentToInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('type')->default('invoice')->after('reference_number');
            $table->boolean('is_email_sent')->default(false)->after('status');
            $table->string('payment_status')->default('unpaid')->after('status');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_email_sent']);
        });
    }
}
