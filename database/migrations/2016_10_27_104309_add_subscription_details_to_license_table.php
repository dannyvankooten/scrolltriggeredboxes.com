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
            $table->boolean('auto_renews')->nullable();
            $table->string('interval')->nullable();
            $table->string('plan')->nullable();
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
            $table->dropColumn('stripe_subscription_id');
            $table->dropColumn('auto_renews');
            $table->dropColumn('interval');
            $table->dropColumn('plan');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('license_id');
        });
    }
}
