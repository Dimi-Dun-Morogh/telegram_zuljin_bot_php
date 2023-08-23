<?php
declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";
use Bot\Bot;
use Services\JokeService;
use Dotenv\Dotenv;
use Telegram\Telegram;



$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$key = $_ENV['TG_BOT_KEY'];



$bot = new Bot(new  Telegram($key));

$bot->initServices([new JokeService]);

$bot->addCallback(["анекдот", "Анекдот"], [JokeService::class, 'jokesHandler']);


$bot->start();









$setWebHook  = function () {
  global $bot;

  $webHookUrl = $_ENV['WEBHOOK_URL'] . "/zuljin_bot/public/index.php";
  $bot->telegram->setWebHook($webHookUrl);
};


return $setWebHook;
