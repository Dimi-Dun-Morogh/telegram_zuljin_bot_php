<?php

namespace App\Bot;

use App\Handlers\Handlers;
use App\Telegram\Telegram;
use App\Utils\Utils;

class Bot
{
  private $callbacks = [];

  private $services = [];

  public function __construct(public Telegram $telegram, private Handlers $handlers)
  {
  }

  private function proccesUpdate(mixed $update)
  {
    $command = '';
    if (!$update) return;

    if (array_key_exists('message', $update)) {

      $this->onEachMessage($update, $this->telegram);

      $command = $this->getCommand($update);
    }
    if ($update['callback_query']) {

      $command = explode(':', $update['callback_query']['data'])[0];
    }

    else if (array_key_exists('chat_member', $update)) {
       $command = 'chat_member_' . $update['chat_member']['new_chat_member']['status'];
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

  public function addCallback(string|array $onText, string $handler)
  {
    $onText =  is_array($onText) ? $onText : [$onText];
    foreach ($onText  as $command) {
      $this->callbacks[$command] = $handler;
    }
  }
  private function invokeCb(string $command, mixed $update)
  {

    if (array_key_exists($command, $this->callbacks)) {

      $fn = $this->callbacks[$command];

      $this->handlers->$fn($update, $this->telegram);
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
      $params = ['limit' => 1, 'offset' => $lastUpdateId, 'allowed_updates'=>["update_id","chat_member","callback_query","message"]];

      $update = $this->telegram->getUpdates($params);

      if ($update && count($update['result'])) {
        $lastUpdateId = $update['result'][0]['update_id'] + 1;

        $this->proccesUpdate($update['result'][0]);
      }

      Utils::writeLog('update.json', json_encode($update));
      sleep(2);
    }
  }

  // this to force check of db creation or some other esential fn to run on each interraction
  private function onEachMessage(mixed $update, Telegram $telegram)
  {
    $cb = $this->callbacks['onEachMessage'];
    if ($cb) {
      $this->handlers->$cb($update, $telegram);
    }
  }
}
