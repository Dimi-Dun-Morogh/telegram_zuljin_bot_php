<?php

declare(strict_types=1);

require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Dotenv\Dotenv;
use Telegram\Telegram;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$key = $_ENV['TG_BOT_KEY'];



$bot = new Bot(new  Telegram($key));

$bot->addCallback("hello", function (Telegram $tg, mixed $update) {
  $whereTo = $update['message']['chat']['id'];
  $tg->sendMessage("world", $whereTo);
});

$bot->addCallback("new_chat_participant", function (Telegram $tg, mixed $update) {
  $whereTo = $update['message']['chat']['id'];
  $tg->sendMessage("greetings mon", $whereTo);
});

$bot->start();









$setWebHook  = function () {
  global $bot;

  $webHookUrl = $_ENV['WEBHOOK_URL'] . "/zuljin_bot/public/index.php";
  $bot->telegram->setWebHook($webHookUrl);
};


return $setWebHook;
