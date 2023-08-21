<?php

namespace Bot;

use Telegram\Telegram;



class Bot
{

  public function __construct(public Telegram $telegram)
  {
  }

  public function start()
  {
    $update = $this->telegram->getWebhookUpdate();
    if (!$update || !$update['message']) return;
    $fromId = $update['message']['from']['id'];
    $this->telegram->sendMessage('echoing', $fromId);
  }
}
