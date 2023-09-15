<?php

declare(strict_types=1);

namespace App\Utils;

//file_put_contents(__DIR__ . '../../../logs/log.json', $data);

class Utils
{
  static string $baseLogPath = __DIR__ . '../../../../logs/';


  static function dirExists()
  {
    $dir = Utils::$baseLogPath;
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }
  }

  static function writeLog(string  $fileName, $content)
  {
    Utils::dirExists();
    $url = Utils::$baseLogPath . $fileName;
    file_put_contents($url, $content);
  }
}
