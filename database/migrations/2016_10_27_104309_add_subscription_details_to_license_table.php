<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionDetailsToLicenseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable();
            $table->boolean('auto_renew')->nullable();
            $table->string('auto_renew_interval')->nullable();
            $table->string('plan')->nullable();
            $table->string('status')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('license_id')->nullable();
        });


        $sql = 'UPDATE payments p INNER JOIN subscriptions s ON s.id = p.subscription_id SET p.license_id = s.license_id';
        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('auto_renew');
            $table->dropColumn('auto_renew_interval');
            $table->dropColumn('plan');
            $table->dropColumn('status');
            $table->dropColumn('stripe_subscription_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('license_id');
        });
    }
}
