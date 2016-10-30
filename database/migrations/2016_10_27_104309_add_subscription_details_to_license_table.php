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
            $table->string('interval')->nullable();
            $table->string('plan')->nullable();
            $table->string('status')->nullable();
            $table->dropSoftDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('license_id')->nullable();
        });


        /** @var PDO $db */
        $db = DB::connection()->getPdo();

        // move license_id from subscription to payment table.
        $sql = 'UPDATE payments p INNER JOIN subscriptions s ON s.id = p.subscription_id SET p.license_id = s.license_id';
        $db->exec($sql);

        // move interval from subscription to license table
        $sql = 'UPDATE licenses l INNER JOIN subscriptions s ON l.id = s.license_id SET l.interval = s.interval';
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
            $table->dropColumn('interval');
            $table->dropColumn('plan');
            $table->dropColumn('status');
            $table->dropColumn('stripe_subscription_id');
            $table->softDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('license_id');
        });
    }
}
