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
$bot->addCallback(["зул вк", 'vk_next_post'], 'sfPostHandler');
$bot->addCallback(["зул игры", 'vk_next_game'],'gamesPostHandler');
$bot->addCallback(["зул нюдсы", 'lrg_next_post'],'lrgPostHandler');
$bot->addCallback(["help", "/help"], 'helpHandler');
$bot->addCallback(["погода"],'weatherHandler');


$bot->start();

if (getenv('APP_MODE') === 'DEV') {
  $bot->longPolling();
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
