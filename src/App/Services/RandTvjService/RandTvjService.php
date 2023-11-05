<?php

namespace App\Services\RandTvjService;

use App\Db\Db;
use App\Telegram\Telegram;


class RandTvjService
{



  public function __construct(private Db $db)
  {

  }

  public function randomLine()
  {
    $totalSongs = $this->db->query('SELECT count(*) as total FROM songs');
    $totalSongs = $totalSongs->find();

    $number = rand(1, $totalSongs['total']);

    $song = $this->db->query("SELECT * FROM songs LIMIT $number,1")->find();


    $arrOfLines = explode('<br>', $song['text']);

    $randomKey = array_rand($arrOfLines);
    echo $arrOfLines[$randomKey]. "\n";
    echo $arrOfLines[$randomKey-1]?? $arrOfLines[$randomKey+1];

    return ['song_name'=>$song['name'],
    'line_1'=> mb_strtoupper( $arrOfLines[$randomKey]),
    'line_2'=> mb_strtoupper( $arrOfLines[$randomKey-1]?? $arrOfLines[$randomKey+1]),
    'id'=>$song['id']
  ];
  }

  public function handleRandomLine(mixed $update, Telegram $telegram){
    $from = null;

    $data = $this->randomLine();

    $callBackQueryId = null;
    $isCbQuery = key_exists('callback_query', $update);
    if ($isCbQuery) {
      $callBackQueryId = $update['callback_query']['id'];
      $update = $update['callback_query'];
      $from = $update['from'];
      sleep(1);
    }else {
      $from = $update['message']["from"];
    }


    $userLink = "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a>";
    $msg ="{$userLink} какая ты строчка песни?"
    ."\n\n\n"
    ."<b>{$data['line_1']}</b>" . "\n"
    ."<b>{$data['line_2']}</b>"
    ."\n\n\n 🔎{$data['song_name']}"
    ;

    $keyboard = [
      "inline_keyboard" => [
        [
          [
            "text" => "Какая ты строчка из песни",
            "callback_data" => "tvj"
          ]
        ]
      ]
    ];

    $replyTo = $callBackQueryId? null :  $update['message']['message_id'];
    $chatId = $update['message']['chat']['id'];

    if($callBackQueryId) {
      $telegram->editMessageText($msg, ['chat_id'=>$chatId, "parse_mode"=>'HTML'
    ,'message_id'=>$update['message']['message_id']],$keyboard);
      return;
    }

    $telegram->sendMessage($msg, $chatId, [
      "reply_to_message_id" => $replyTo,"parse_mode"=>'HTML'
    ], $keyboard);
  }
}


