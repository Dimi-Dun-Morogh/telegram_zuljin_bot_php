<?php

declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Services\{CommonService, FreeGamesService, JokeService, VkGroupService};
use Dotenv\Dotenv;
use Utils\Utils;

use Telegram\Telegram;


Utils::writeLog('hook.json', file_get_contents('php://input'));

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$key = $_ENV['TG_BOT_KEY'];



$bot = new Bot(new  Telegram($key));

$bot->initServices([new JokeService, new VkGroupService($_ENV['VK_GRP'], 'vk_next_post'), new FreeGamesService('-196285812', 'vk_next_game'), new CommonService]);

$bot->addCallback(["анекдот", "зул анекдот"], [JokeService::class, 'jokesHandler']);
$bot->addCallback(["зул вк", 'vk_next_post'], [VkGroupService::class, 'getPostHandler']);
$bot->addCallback(["зул игры", 'vk_next_game'], [FreeGamesService::class, 'getPostHandler']);
$bot->addCallback(["help", "/help"], [CommonService::class, 'helpHandler']);


$bot->start();



$setWebHook  = function () {
  global $bot;

  $webHookUrl = $_ENV['WEBHOOK_URL'] . "/zuljin_bot/public/index.php";
  $bot->telegram->setWebHook($webHookUrl);
  //  $bot->telegram->deleteWebHook();
};

$deleteWebHook = function () {
  global $bot;

  $bot->telegram->deleteWebHook();
};

return [$setWebHook, $deleteWebHook];
