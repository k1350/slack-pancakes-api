<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $team = new App\Models\SlackTeam();
        $team->team_id = "";
        $team->incoming_url = "";
        $team->incoming_channel = "#";
        $team->token = "";
        $team->save();

        $slack_user = new App\Models\SlackUser();
        $slack_user->user_id = "";
        $slack_user->user_name = "test1";
        $slack_user->team_id = $team->id;
        $slack_user->save();

        $slack_user2 = new App\Models\SlackUser();
        $slack_user2->user_id = "";
        $slack_user2->user_name = "test2";
        $slack_user2->team_id = $team->id;
        $slack_user2->save();

        $send = new App\Models\SendPancake();
        $send->from_user_id = $slack_user->id;
        $send->number = 2;
        $send->to_user_id = $slack_user2->id;
        $send->message = "テスト";
        $send->save();

        $total = new App\Models\TotalPancake();
        $total->user_id = $slack_user->id;
        $total->received = 0;
        $total->sent = 2;
        $total->used = 0;
        $total->save();

        $total2 = new App\Models\TotalPancake();
        $total2->user_id = $slack_user2->id;
        $total2->received = 2;
        $total2->sent = 0;
        $total2->used = 0;
        $total2->save();


        // $this->call(UsersTableSeeder::class);
    }
}
