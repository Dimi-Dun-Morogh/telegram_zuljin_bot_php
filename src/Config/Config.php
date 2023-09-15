<?php

namespace Config;
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . "/../../");
$dotenv->safeload();


class Config
{

  static function  AppMode()
  {
    return  getenv('APP_MODE');
  }

  static function BotKey():string
  {
  return  getenv('APP_MODE') === 'DEV' ? getenv('DEV_BOT_KEY') :   getenv('TG_BOT_KEY');
  }

  static  function WebhookUrl()
  {
    return Config::AppMode() === 'DEV' ? getenv('WEBHOOK_URL') . "/zuljin_bot/public/index.php" :
      getenv('WEBHOOK_URL') . "/public/index.php";
  }
  static function dbConfig():array{
    return [
      'host' => getenv('DB_HOST'),
      'port' => getenv('DB_PORT'),
      'dbname' => getenv('DB_NAME'),
      'user' => getenv('DB_USER'),
      'pass' => getenv('DB_PASS'),
    ];
  }
}
