<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddMoneybirdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->string('moneybird_contact_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('moneybird_invoice_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moneybird_contact_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('moneybird_invoice_id');
        });
    }
}
