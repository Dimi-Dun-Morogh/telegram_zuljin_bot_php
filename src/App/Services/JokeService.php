<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use App\Telegram\Telegram;

class JokeService
{
  private  function getJoke(): string
  {

    $res = [];
    $url = "https://www.anekdot.ru/random/anekdot/";

    $doc = new DOMDocument();
    $doc->loadHTMLFile($url);
    foreach ($doc->getElementsByTagName('div') as $item) {
      $class =  $item->getAttribute('class');
      if ($class === 'text') {
        $text = $item->nodeValue;
        $res[] = $text;
      }
    }
    return (string) $res[rand(0, count($res) - 1)];
  }

  public function jokesHandler(mixed $update, Telegram $telegram)
  {
    $joke = $this->getJoke();
    $userString = null;
    $callBackQueryId = null;
    $from = null;
    // if handler invoked from button press
    $isCbQuery = key_exists('callback_query', $update);
    if ($isCbQuery) {
      $callBackQueryId = $update['callback_query']['id'];
      $update = $update['callback_query'];
      $from = $update['from'];
      $whoPressed = "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a> нажал на кнопку"
      .  "\r\n" . "\r\n";;

    $telegram->editMessageText($whoPressed . "Just a second 'Mon, da Zul be working on dat task right now", [
      'chat_id' => $update['message']['chat']['id'],
      'message_id' => $update['message']['message_id'],
      'parse_mode' => 'HTML',
    ]);

    sleep(1);
    }else {
      $from = $update['message']["from"];
    }

    $replyTo = $callBackQueryId? null :  $update['message']['message_id'];
    $chatId = $update['message']['chat']['id'];
    $userString = "Анекдот для " . "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a>"  . "\r\n" . "\r\n";
   // $userString = "";
    $keyboard = [
      "inline_keyboard" => [
        [
          [
            "text" => "Ещё",
            "callback_data" => "анекдот"
          ]
        ]
      ]
    ];

    if($isCbQuery) {
      $telegram->editMessageText("$userString <b>$joke</b>", ['chat_id'=>$chatId,'message_id'=>$update['message']['message_id'],
      'parse_mode'=> 'HTML',
    ], $keyboard);

      $telegram->answerCallbackQuery($callBackQueryId);
      return;
    }

    $telegram->sendMessage("$userString <b>$joke</b>", $chatId, [
      "reply_to_message_id" => $replyTo,"parse_mode"=>'HTML'
    ], $keyboard);

    // if($isCbQuery) {
    //   $telegram->answerCallbackQuery($callBackQueryId);
    // }
  }
}
