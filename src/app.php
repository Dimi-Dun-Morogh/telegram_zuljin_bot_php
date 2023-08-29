<?php

declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Services\{CommonService, FreeGamesService, JokeService, VkGroupService, WeatherService};
use Dotenv\Dotenv;
use Utils\Utils;

use Telegram\Telegram;



Utils::writeLog('hook.json', file_get_contents('php://input'));

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . "/../");
$dotenv->safeload();

$key = getenv('APP_MODE') === 'DEV' ? getenv('DEV_BOT_KEY') :   getenv('TG_BOT_KEY');




$bot = new Bot(new  Telegram($key));

$bot->initServices([new JokeService, new VkGroupService(getenv('VK_GRP'), 'vk_next_post'), new FreeGamesService('-196285812', 'vk_next_game'), new CommonService, new WeatherService]);



$bot->addCallback(["анекдот", "зул анекдот"], [JokeService::class, 'jokesHandler']);
$bot->addCallback(["зул вк", 'vk_next_post'], [VkGroupService::class, 'getPostHandler']);
$bot->addCallback(["зул игры", 'vk_next_game'], [FreeGamesService::class, 'getPostHandler']);
$bot->addCallback(["help", "/help"], [CommonService::class, 'helpHandler']);
$bot->addCallback(["погода"], [WeatherService::class, 'weatherHandler']);


$bot->start();

$bot->longPolling();

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
