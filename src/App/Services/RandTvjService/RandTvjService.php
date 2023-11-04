<?php

namespace App\Services\RandTvjService;

use App\Telegram\Telegram;
use \SQLite3;

class RandTvjService
{

  private $db;

  public function __construct()
  {
    $this->db = new SQLite3(__DIR__ . '/database.db');
  }

  public function randomLine()
  {
    $totalSongs = $this->db->query('SELECT count(*) as total FROM songs');
    $totalSongs = $totalSongs->fetchArray(SQLITE3_ASSOC)['total'];

    $number = rand(1, $totalSongs['total']);

    $song = $this->db->query("SELECT * FROM songs LIMIT $number,1");
    $song = $song->fetchArray(SQLITE3_ASSOC);

    $arrOfLines = explode('<br>', $song['text']);

    $randomKey = array_rand($arrOfLines);
    echo $arrOfLines[$randomKey]. "\n";
    echo $arrOfLines[$randomKey-1]?? $arrOfLines[$randomKey+1];

    return ['song_name'=>$song['name'],
    'line_1'=> mb_strtoupper( $arrOfLines[$randomKey]),
    'line_2'=> mb_strtoupper( $arrOfLines[$randomKey-1]?? $arrOfLines[$randomKey+1])
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
    $msg ="{$userLink} какая ты строчка ТВЖ?"
    ."\n\n\n"
    ."<b>{$data['line_1']}</b>" . "\n"
    // ."<b>{$data['line_2']}</b>"
    ;

    $keyboard = [
      "inline_keyboard" => [
        [
          [
            "text" => "Какая ты строчка из твж",
            "callback_data" => "tvj"
          ]
        ]
      ]
    ];

    $replyTo = $callBackQueryId? null :  $update['message']['message_id'];
    $chatId = $update['message']['chat']['id'];

    $telegram->sendMessage($msg, $chatId, [
      "reply_to_message_id" => $replyTo,"parse_mode"=>'HTML'
    ], $keyboard);
  }
}


