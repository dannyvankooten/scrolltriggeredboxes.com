<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterPluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plugins', function ($table) {

            // drop obsolete columns
            $table->dropColumn('author');
            $table->dropColumn('version');
            $table->dropColumn('upgrade_notice');
            $table->dropColumn('requires');
            $table->dropColumn('tested');
            $table->dropColumn('changelog');
            $table->dropColumn('external_url');

            // add github column
            $table->string('github_repo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plugins', function ($table) {
            $table->string('version');
            $table->string('author')->default('');
            $table->text('changelog')->default('');
            $table->string('external_url')->default('');
            $table->text('upgrade_notice')->nullable();
            $table->string('requires')->nullable();
            $table->string('tested')->nullable();

            $table->dropColumn('github_repo');
        });
    }
}
