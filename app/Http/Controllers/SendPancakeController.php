<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SendPancake;
use App\Models\SlackTeam;
use App\Models\SlackUser;
use App\Models\TotalPancake;

class SendPancakeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return json_encode("index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // チームIDとトークンの一致を確認
            $team = SlackTeam::where([
                ['team_id', '=', $request->input('team_id')],
                ['token', '=', $request->input('token')]
            ])->first();
            
            if (empty($team)) {
                return $this->makeErrorResponse(__('messages.notRegisteredError', ['name', 'team: '. $request->input('team_name')]));
            }

            // ユーザーを取得
            $from_user_id = $request->input('user_id');
            $from_user = SlackUser::where([
                ['user_id', '=', $from_user_id],
                ['team_id', '=', $team->id]
            ])->first();

            if (empty($from_user)) {
                return $this->makeErrorResponse(__('messages.notRegisteredError', ['name', 'user: you']));
            }

            // コマンドの解析
            $text = $request->input('text');
            $args = explode(" ", $text);

            if (count($args) < 3) {
                return $this->makeErrorResponse(__('messages.argumentsError'));
            }

            // 送信先ユーザーIDを取り出す
            $to_user_id_array = explode("|", substr($args[0], 2));
            $to_user_id = $to_user_id_array[0];
            // 送信先ユーザーを取得
            $to_user = SlackUser::where([
                ['user_id', '=', $to_user_id],
                ['team_id', '=', $team->id]
            ])->first();

            if (empty($to_user)) {
                return $this->makeErrorResponse(__('messages.notRegisteredError', ['name', 'user: ' . $args[0]]));
            }

            // 個数
            $number = intval($args[1]);

            // 送信済みの個数
            $sent_number = SendPancake::where([
                ['from_user_id', '=', $from_user->id],
                ['created_at', '>=', date('Y-m-d') . ' 00:00:00']
            ])->sum('number');

            // 送れる個数
            $max_number = intval(config('constant.pancakes'));

            if ($sent_number + $number > $max_number) {
                return $this->makeResponse(array(
                    'attachments' => array(
                        'color' => __('messages.danger'),
                        'text' => __('messages.overError', ['number' => $max_number - $sent_number])
                    )
                ));
            }

            // メッセージ
            $message = $args[2];

            // DBに登録
            $db_result = $this->cascadeSave($from_user->id, $number, $to_user->id, $message);

            if (!$db_result) {
                return $this->makeErrorResponse(__('messages.dbError'));
            }
            
            // 結果配列作成
            $response_array = array();
            $response_array['text'] = __('messages.message', ['to_user' => $args[0], 'number' => $number, 'from_user_id' => $from_user_id]);
            $response_array['channel'] = $team->incoming_channel;
            $response_array = array_merge($response_array, [
                'attachments' => [array('text' => $message)]
            ]);
            
            $this->sendToSlack($response_array, $team->incoming_url);


            $command_response = array();
            return $this->makeResponse(array_merge($command_response, [
                'attachments' => [array(
                    'color' => __('messages.good'),
                    'text' => __('messages.pancakeSuccess')
                )]
            ]));
            
        } catch (Exception $e) {
            report($e);
            return $this->makeResponse(array(
                'attachments' => array(
                    'color' => __('messages.danger'),
                    'text' => __('messages.invalidDataError')
                )
            ));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * エラーメッセージの作成
     * @param string $message
     */
    private function makeErrorResponse($message) {
        $command_response = array();
        return $this->makeResponse(array_merge($command_response, [
            'attachments' => [array(
                'color' => __('messages.danger'),
                'text' => $message
            )]
        ]));
    }

    /**
     * Slackの返信用配列を作る
     * @param string $text
     * @param string $channel
     * @param array $attachments
     */
    private function makeResponseArray($text, $channel, $attachments = null) {
        $response_array = array();
        $response_array['text'] = $text;
        $response_array['channel'] = $channel;
        if (!empty($attachments)) {
            $response_array = array_merge($response_array, [
                'attachments' => [$attachments]
            ]);
        }
        return $response_array;    
    }

    /**
     * テキストから返答JSONを作る
     * @param string $message
     * @param string $color
     */
    private function makeResponse($text) {
        header('Content-Type: application/json');
        return json_encode($text);
    }

    /**
     * 送信パンケーキと合計パンケーキを更新
     * @param int $from_user_id
     * @param int $number
     * @param int $to_user_id
     * @param string $message
     */
    private function cascadeSave($from_user_id, $number, $to_user_id, $message) {
        try {
            DB::transaction(function () use ($from_user_id, $number, $to_user_id, $message) {
                $send_pancake = new SendPancake;
                $send_pancake->from_user_id = $from_user_id;
                $send_pancake->number = $number;
                $send_pancake->to_user_id = $to_user_id;
                $send_pancake->message = $message;
                $send_pancake->save();

                $from_total_pancake = TotalPancake::where('user_id', '=', $from_user_id)->first();
                $from_total_pancake->sent = intval($from_total_pancake->sent) + $number;
                $from_total_pancake->save();

                $to_total_pancake = TotalPancake::where('user_id', '=', $to_user_id)->first();
                $to_total_pancake->received = intval($to_total_pancake->received) + $number;
                $to_total_pancake->save();

            });

            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
        
    }

    /**
     * Slackにポスト
     * @param string $message
     * @param string $url
     */
    private function sendToSlack($message, $url) {
        $options = array(
          'http' => array(
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($message),
          )
        );
        $response = file_get_contents($url, false, stream_context_create($options));
        return $response === 'ok';
    }
      
}
