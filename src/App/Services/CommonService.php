<?php

namespace App\Services;

use App\Telegram\Telegram;

class CommonService
{
  public function helpHandler(mixed $update, Telegram $telegram)
  {
    $chatId = $update['message']['chat']['id'];
    $telegram->sendMessage("Привет, я Зул Джин
Напиши мне:
анекдот - расскажу анекдот
погода cityname  - погода
зул игры - отправлю свежие раздачи игр
зул мемы - отправлю мем
твж - какая ты строчка из песни
кто (текст) - узнай кто в чате (текст)
когда (текст) - узнай когда (текст)
инфа (текст) - узнай вероятность (текст)
      ", $chatId);
  }
}
