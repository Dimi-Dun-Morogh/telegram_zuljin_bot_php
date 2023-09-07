<?php

declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Dotenv\Dotenv;
use Handlers\Handlers;
use Utils\Utils;

use Telegram\Telegram;


Utils::writeLog('hook.json', file_get_contents('php://input'));

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . "/../");
$dotenv->safeload();

$key = getenv('APP_MODE') === 'DEV' ? getenv('DEV_BOT_KEY') :   getenv('TG_BOT_KEY');

$bot = new Bot(new  Telegram($key), new Handlers);

$bot->addCallback(["анекдот", "зул анекдот"], 'jokesHandler');
$bot->addCallback(["зул вк", 'vk_next_post', 'vk_next_postrandom'], 'sfPostHandler');
$bot->addCallback(["зул игры", 'vk_next_game'], 'gamesPostHandler');
$bot->addCallback(["зул нюдсы", 'lrg_next_post','lrg_next_postrandom'], 'lrgPostHandler');
$bot->addCallback(["зул мем", "зул мемы", 'mem_next_post', 'mem_next_postrandom'], 'memsPostHandler');
$bot->addCallback(["help", "/help", "/start", "start"], 'helpHandler');
$bot->addCallback(["погода"], 'weatherHandler');

try {
  $bot->start();

  if (getenv('APP_MODE') === 'DEV') {
//    $bot->longPolling();
  }
} catch (\Throwable $th) {
  Utils::writeLog('error.txt',   $th->getMessage());
}




$setWebHook  = function () {
  global $bot;
  $appMode = getenv('APP_MODE');
  $webHookUrl = getenv('WEBHOOK_URL');
  if ($appMode == 'DEV') {
    $webHookUrl = $webHookUrl . "/zuljin_bot/public/index.php";
  } else {
    $webHookUrl = $webHookUrl .  "/public/index.php";
  }

  $bot->telegram->setWebHook($webHookUrl);
};

$deleteWebHook = function () {
  global $bot;
  $bot->telegram->deleteWebHook();
};



return [$setWebHook, $deleteWebHook];
