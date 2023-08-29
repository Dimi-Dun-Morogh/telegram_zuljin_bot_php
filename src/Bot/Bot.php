<?php

namespace Bot;

use Telegram\Telegram;
use Utils\Utils;

class Bot
{
  private $callbacks = [];

  private $services = [];

  public function __construct(public Telegram $telegram)
  {
  }

  private function proccesUpdate( mixed $update) {
    $command = '';
    if (!$update) return;
    if ($update && $update['message']) {
      $command = $this->getCommand($update);
    } else if ($update && $update['callback_query']) {
      // extract param

      $command = explode(':', $update['callback_query']['data'])[0];
    };


    $this->invokeCb($command, $update);
  }

  public function start()
  {
    $update = $this->telegram->getWebhookUpdate();
    $this->proccesUpdate($update);
  }

  private function getCommand(mixed $update)
  {
    $infoKeys = ['text', 'new_chat_participant', 'left_chat_member'];
    $message = $update['message'];
    $command = '';
    foreach ($infoKeys as $key) {
      if (array_key_exists($key, $message)) {

        if ($key === 'text') {
          $command = explode('@', mb_strtolower($message['text']))[0];
          $command = $this->callbacks[$command] ? $command : explode(' ', $command)[0];
        } else {
          $command = $key;
        }

        break;
      }
    }

    return $command;
  }

  public function addCallback(string|array $onText, array $handler)
  {
    $onText =  is_array($onText) ? $onText : [$onText];
    foreach ($onText  as $command) {
      $this->callbacks[$command] = $handler;
    }
  }
  private function invokeCb(string $command, mixed $update)
  {
    if ($this->callbacks[$command]) {

      [$className, $methodName] = $this->callbacks[$command];


      $this->services[$className]->$methodName($update, $this->telegram);
    }
  }
  public function initServices(array $services)
  {
    foreach ($services as $service) {
      $this->services[$service::class] = $service;
    }
  }

  public function longPolling()
  {
    $lastUpdateId = null;



    while (true) {
      $params = ['limit' => 1, 'offset' => $lastUpdateId];

      $update = $this->telegram->getUpdates($params);

      if($update && count($update['result'])) {
        $lastUpdateId = $update['result'][0]['update_id'] +1;

        $this->proccesUpdate($update['result'][0]);

        // $this->telegram->sendMessage("$lastUpdateId",$update['result'][0]['message']['chat']['id']);
      }

      Utils::writeLog('update.json', json_encode($update));
      sleep(2);
    }
  }
}
