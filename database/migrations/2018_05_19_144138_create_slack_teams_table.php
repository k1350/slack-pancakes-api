<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlackTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('team_id');
            $table->string('incoming_url');
            $table->string('incoming_channel');
            $table->string('token');
            $table->string('enterprise_id')->nullable();
            $table->timestamps();

            $table->unique('team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slack_teams');
    }
}
