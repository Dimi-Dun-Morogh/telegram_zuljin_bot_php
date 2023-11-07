<?php

declare(strict_types=1);


namespace App\Utils;
use App\Db\Db;


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

  static function writeLog(string  $fileName, $content, Db $db=null)
  {
    Utils::dirExists();
    $url = Utils::$baseLogPath . $fileName;
    file_put_contents($url, $content);
    if($db){
      $db->query("INSERT into errors (`text`) VALUES (:t)", ['t'=>$content]);
    }
  }
}
