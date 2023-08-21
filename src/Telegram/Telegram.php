<?php

declare(strict_types=1);

namespace Telegram;

class Telegram
{
  private string $baseUrl = 'http://api.telegram.org/bot';

  public function __construct(string $apiKey)
  {
    $this->baseUrl = $this->baseUrl . $apiKey;
  }

  private function api(string $method, array $params = [])
  {
    $url = $this->baseUrl . "/" . $method;
    if (!empty($params)) {
      $url = $url . "?" . http_build_query($params);
    }

    $data = file_get_contents($url);
    if ($data) {
      $data = json_decode($data, true);
    }
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    return $data;
  }

  public function getUpdates()
  {
    $data = $this->api('getUpdates');
    return $data;
  }
  public function sendMessage(string $message, string|int $chatId)
  {
    $this->api(
      'sendMessage',
      ['chat_id' => $chatId, 'text' => $message]
    );
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
}
