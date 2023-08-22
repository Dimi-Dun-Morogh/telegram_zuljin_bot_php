<?php

namespace Bot;

use Telegram\Telegram;



class Bot
{
  private $callbacks = [];

  public function __construct(public Telegram $telegram)
  {
  }

  public function start()
  {
    $update = $this->telegram->getWebhookUpdate();
    if (!$update || !$update['message']) return;
    $command = $this->getCommand($update);

    $this->invokeCb($command, $update);

  }

  private function getCommand(mixed $update) {
    $infoKeys = ['text', 'new_chat_participant', 'left_chat_member'];
    $message = $update['message'];
    $command = '';
    foreach($infoKeys as $key) {
      if(array_key_exists($key, $message)) {
       $command = $key === 'text' ?  $message[$key] : $key;
       break;
      }
    }
    return $command;

  }

  public function addCallback(string $onText, callable $cb){
    $this->callbacks[$onText]  = $cb;
  }
  private function invokeCb(string $command, mixed $update){

    if($this->callbacks[$command]) {
      $fnc = $this->callbacks[$command];

      $fnc($this->telegram,  $update);

    }
  }
}
