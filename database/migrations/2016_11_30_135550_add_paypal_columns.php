<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaypalColumns extends Migration
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
            $table->string('paypal_subscription_id')->nullable();

        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('paypal_id')->nullable();
            $table->string('stripe_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
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
            $table->dropColumn('paypal_subscription_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('paypal_id');
            $table->string('stripe_id')->change();

        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
}
