<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountColumnsForTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_amount')->default(0);
            $table->integer('current_amount')->default(0);
            $table->integer('seven_days_amount')->default(0);
            $table->integer('fukkatu')->default(0);
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
            $table->dropColumn('total_amount');
            $table->dropColumn('current_amount');
            $table->dropColumn('seven_days_amount');
            $table->dropColumn('fukkatu');
        });
    }
}
