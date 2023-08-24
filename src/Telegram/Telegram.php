<?php

declare(strict_types=1);

namespace Telegram;

use Exception;

class Telegram
{
  public string $baseUrl = 'http://api.telegram.org/bot';

  public function __construct(string $apiKey)
  {
    $this->baseUrl = $this->baseUrl . $apiKey;
  }

  private function api(string $method, array $params = [], array $keyboard = [])
  {


    //   header('Content-Type: application/json;charset=utf-8');


    $url = $this->baseUrl . "/" . $method;
    if (!empty($params)) {
      $url = $url . "?" . http_build_query($params);
    }

    if (!empty($keyboard)) {
      $keyboard = json_encode($keyboard);
      $url = $url . "&reply_markup=$keyboard";
    }

    $data = file_get_contents($url);
    file_put_contents(__DIR__ . '../../../log.json', $data);
    if ($data) {
      $data = json_decode($data, true);
    }

    if ($data === false) {
      // An error occurred, write error message to file
      $error = error_get_last();
      $message = $error['message'];
      file_put_contents(__DIR__ . '../../../logerror.txt', $message);
    }
    var_dump($data);

    return $data;
  }

  public function getUpdates()
  {
    $data = $this->api('getUpdates');
    return $data;
  }
  public function sendMessage(string $message, string|int $chatId, array $params = [], array $keyboard = [])
  {
    $url = $this->baseUrl . "/sendMessage?"  . "&text=$message&chat_id=$chatId&" . http_build_query($params);
    if (!empty($keyboard)) {
      $keyboard = json_encode($keyboard);
      $url = $url . "&reply_markup=$keyboard";
    }
    file_get_contents($url);

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

  public function answerCallbackQuery(int|string $id, array $params = [])
  {
    $this->api('answerCallbackQuery', array_merge(['callback_query_id' => $id], $params));
  }

  public function sendPhoto(string $imgUrl, string $captions =  '',  array $params)
  { $url  = $this->baseUrl  . "/sendPhoto?" . "&photo=" .  urlencode($imgUrl)
    ."&caption=" . $captions . "&" . http_build_query($params);

    file_get_contents($url);
  }
}
