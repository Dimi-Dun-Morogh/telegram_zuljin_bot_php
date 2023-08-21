<?php

declare(strict_types=1);

require __DIR__ . "/../vendor/autoload.php";

use Bot\Bot;
use Dotenv\Dotenv;
use Telegram\Telegram;

$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$key = $_ENV['TG_BOT_KEY'];

//dev purp
$ngrockUrl =  "https://67e7-78-26-242-170.ngrok-free.app";

$webhookurl = $ngrockUrl . "/zuljin_bot/public/index.php";

$bot = new Bot(new  Telegram($key));
$bot->start();
