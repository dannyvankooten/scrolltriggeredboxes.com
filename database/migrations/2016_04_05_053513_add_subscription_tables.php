<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('card_last_four')->nullable();
            $table->string('stripe_customer_id')->nullable();
        });

        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->boolean('active')->default(false);
            $table->decimal('amount', 10, 2);
            $table->enum( 'interval', [ 'month', 'year' ]);
            $table->timestamp('next_charge_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users', function ($table) {
            $table->dropColumn('card_last_four');
            $table->dropColumn('stripe_customer_id');
        });

        Schema::drop('subscriptions');
    }
}
