<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendPancakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_pancakes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_user_id');
            $table->unsignedInteger('number');
            $table->unsignedInteger('to_user_id');
            $table->text('message');
            $table->timestamps();

            $table->foreign('from_user_id')->references('id')->on('slack_users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('slack_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_pancakes');
    }
}
