<?php

namespace Services;

use Telegram\Telegram;

class CommonService
{
  public function helpHandler(mixed $update, Telegram $telegram)
  {
    $chatId = $update['message']['chat']['id'];
    $telegram->sendMessage("Привет, я Зул Джин
Напиши мне:
анекдот - расскажу анекдот
зул игры - отправлю свежие раздачи игр
      ", $chatId);
  }
}