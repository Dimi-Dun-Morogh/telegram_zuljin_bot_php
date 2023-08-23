<?php

declare(strict_types=1);

namespace Telegram;

use Exception;

class Telegram
{
  private string $baseUrl = 'http://api.telegram.org/bot';

  public function __construct(string $apiKey)
  {
    $this->baseUrl = $this->baseUrl . $apiKey;
  }

  private function api(string $method, array $params = [], array $keyboard = [])
  {

    try {
      $url = $this->baseUrl . "/" . $method;
      if (!empty($params)) {
        $url = $url . "?" . http_build_query($params)  ;
      }

      if(!empty($keyboard))  {
        $keyboard = json_encode($keyboard);
        $url = $url ."&reply_markup=$keyboard";
      }

      $data = file_get_contents($url);
      if ($data) {
        $data = json_decode($data, true);
      }
      return $data;
    }
    catch(Exception $e){
      file_put_contents(__DIR__ . '../../../log_err.txt', json_encode($e));
    }


  }

  public function getUpdates()
  {
    $data = $this->api('getUpdates');
    return $data;
  }
  public function sendMessage(string $message, string|int $chatId, array $params = [], array $keyboard = [])
  {
    $this->api(
      'sendMessage',
      array_merge(['chat_id' => $chatId, 'text' => $message], $params)
    , $keyboard);
  }

  public function setWebHook(string $url)
  {
    return  $this->api('setWebhook', ['url' => $url]);
  }

  public function deleteWebHook()
  {
    $this->api('deleteWebhook');
  }

  public function getWebhookUpdate()
  {
    file_put_contents(__DIR__ . '../../../log.json', file_get_contents('php://input'));

    $update = json_decode(file_get_contents('php://input'), true);
    return $update;
  }

  public function answerCallbackQuery (int|string $id, array $params = []){
    $this->api('answerCallbackQuery', array_merge(['callback_query_id'=>$id], $params));
  } 

}
