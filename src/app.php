<?php

declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Services\{JokeService, VkGroupService};
use Dotenv\Dotenv;
use Utils\Utils;

use Telegram\Telegram;


Utils::writeLog('hook.json', file_get_contents('php://input'));

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$key = $_ENV['TG_BOT_KEY'];



$bot = new Bot(new  Telegram($key));

$bot->initServices([new JokeService, new VkGroupService]);

$bot->addCallback(["анекдот", "зул анекдот"], [JokeService::class, 'jokesHandler']);
$bot->addCallback(["зул вк", 'vk_next_post'], [VkGroupService::class, 'getPostHandler']);

$bot->start();



$setWebHook  = function () {
  global $bot;

  $webHookUrl = $_ENV['WEBHOOK_URL'] . "/zuljin_bot/public/index.php";
   $bot->telegram->setWebHook($webHookUrl);
  // $bot->telegram->deleteWebHook();
};


return $setWebHook;

