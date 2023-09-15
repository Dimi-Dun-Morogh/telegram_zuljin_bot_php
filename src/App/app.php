<?php

declare(strict_types=1);
require __DIR__ . "/../../vendor/autoload.php";

use App\Bot\Bot;
use App\Handlers\Handlers;
use App\Utils\Utils;
use Config\Config;
use App\Telegram\Telegram;




Utils::writeLog('hook.json', file_get_contents('php://input'));

$bot = new Bot(new  Telegram(Config::BotKey()), new Handlers);

$bot->addCallback(["анекдот", "зул анекдот"], 'jokesHandler');
$bot->addCallback(["зул вк", 'vk_next_post', 'vk_next_postrandom'], 'sfPostHandler');
$bot->addCallback(["зул игры", 'vk_next_game'], 'gamesPostHandler');
$bot->addCallback(["зул нюдсы", 'lrg_next_post', 'lrg_next_postrandom'], 'lrgPostHandler');
$bot->addCallback(["зул мем", "зул мемы", 'mem_next_post', 'mem_next_postrandom'], 'memsPostHandler');
$bot->addCallback(["help", "/help", "/start", "start"], 'helpHandler');
$bot->addCallback(["погода"], 'weatherHandler');

try {
  $bot->start();

  if (Config::AppMode() === 'DEV') {

    $bot->longPolling();
  }
} catch (\Throwable $th) {
  Utils::writeLog('error.txt',   $th->getMessage());
}




$setWebHook  = function () {
  global $bot;
  $webHookUrl = Config::WebhookUrl();
  $bot->telegram->setWebHook($webHookUrl);
};

$deleteWebHook = function () {
  global $bot;
  $bot->telegram->deleteWebHook();
};


return [$setWebHook, $deleteWebHook];
