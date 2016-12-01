<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBraintreeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('payment_method')->default('stripe');
            $table->string('braintree_subscription_id')->nullable();

        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('braintree_id')->nullable();
            $table->string('stripe_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('braintree_customer_id')->nullable();
            $table->string('braintree_payment_token')->nullable();
            $table->string('payment_method')->default('stripe');
        });

        // move license_id from subscription to payment table.
        /** @var PDO $db */
        $db = DB::connection()->getPdo();

        $sql = "UPDATE licenses SET payment_method = 'stripe' WHERE payment_method IS NULL";
        $db->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('braintree_subscription_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('braintree_id');
            $table->string('stripe_id')->change();

        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('braintree_customer_id');
            $table->dropColumn('braintree_payment_token');
        });
    }
}
