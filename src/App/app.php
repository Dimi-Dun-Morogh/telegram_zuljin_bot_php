<?php

declare (strict_types = 1);
require __DIR__ . "/../../vendor/autoload.php";

use App\Bot\Bot;
use App\Db\Db;
use App\Handlers\Handlers;
use App\Services\AdminService;
use App\Telegram\Telegram;
use App\Utils\Utils;
use Config\Config;

$dbConfig = Config::dbConfig();

$db = new Db('mysql', [
	'host' => $dbConfig['host'],
	'port' => $dbConfig['port'],
	'dbname' => $dbConfig['dbname'],
], $dbConfig['user'], $dbConfig['pass']);

$test = 1;

$admin = new AdminService($db);

$bot = new Bot(new Telegram(Config::BotKey(), $db), new Handlers($db));

$bot->addCallback(["анекдот", "зул анекдот"], 'jokesHandler');
$bot->addCallback(["зул вк", 'vk_next_post', 'vk_next_postrandom'], 'sfPostHandler');
$bot->addCallback(["зул игры", 'vk_next_game'], 'gamesPostHandler');
$bot->addCallback(["зул нюдсы", 'lrg_next_post', 'lrg_next_postrandom'], 'lrgPostHandler');
$bot->addCallback(["зул мем", "зул мемы", 'mem_next_post', 'mem_next_postrandom'], 'memsPostHandler');
$bot->addCallback(["help", "/help"], 'helpHandler');
$bot->addCallback(["погода"], 'weatherHandler');
$bot->addCallback([ "/start", "start"], 'startHandler');
$bot->addCallback(["/rules_add"], "updateRulesHandler");
$bot->addCallback(["/greet_add"], "updateGreetHandler");
$bot->addCallback(["/leave_add"], "updateLeaveHandler");
$bot->addCallback(['new_chat_participant'], "onChatEnterHandler");
$bot->addCallback(['left_chat_member'], "onChatLeaveHandler");
$bot->addCallback(['зул стата'], "msgStatHandler");
$bot->addCallback(['твж', 'Твж', 'tvj'], "tvjHandler");

$bot->addCallback('onEachMessage', 'onEachMessageHandler');



try {
	$bot->start();

	if (Config::AppMode() === 'DEV') {

		  // $bot->longPolling();
	}
} catch (\Throwable $th) {
	Utils::writeLog('error.txt', $th->getMessage(), $db);
}


return ['admin' => $admin, 'bot' => $bot, 'config' => ['WebHook' => Config::WebhookUrl(), 'botkey' => Config::BotKey()]];

