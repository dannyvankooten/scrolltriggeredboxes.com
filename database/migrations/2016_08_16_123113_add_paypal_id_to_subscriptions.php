<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddPaypalIdToSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
           $table->string('paypal_id')->nullable();

        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('paypal_id')->nullable();
            $table->string('stripe_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('paypal_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('paypal_id');
            $table->string('stripe_id')->change();
        });
    }
}
