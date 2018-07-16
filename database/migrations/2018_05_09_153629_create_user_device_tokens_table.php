<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDeviceTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_device_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->char('platform', 20)->default('android');
            $table->string('device_token')->nullable();
            $table->string('arn');
            $table->string('subscription_arn')->nullable();
            $table->integer('user_id')->index();
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
        Schema::dropIfExists('user_device_tokens');
    }
}
