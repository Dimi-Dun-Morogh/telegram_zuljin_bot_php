<?php

declare(strict_types=1);

namespace Services;

use DOMDocument;
use Telegram\Telegram;

class JokeService
{
  private  function getJoke(): string
  {
    sleep(3);
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
    $replyTo = null;
    $callBackQueryId = null;
    // if handler invoked from button press
    $isCbQuery = key_exists('callback_query', $update);
    if ($isCbQuery) {
      $callBackQueryId = ['update']['callback_query']['id'];


      $update = $update['callback_query'];
      $replyTo = $update['message']['reply_to_message']['message_id'];
    } else {
      $replyTo = $update['message']['message_id'];
    }

    $chatId = $update['message']['chat']['id'];

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

    $telegram->sendMessage("<b>$joke</b>", $chatId, [
      "reply_to_message_id" => $replyTo,"parse_mode"=>'HTML'
    ], $keyboard);

    if($isCbQuery) {
      $telegram->answerCallbackQuery($callBackQueryId);
    }
  }
}
