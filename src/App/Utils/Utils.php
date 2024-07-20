<?php

declare(strict_types=1);


namespace App\Utils;
use App\Db\Db;
use DateInterval;
use Config\Config;

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

    if($db){
      $db->query("INSERT into errors (`text`) VALUES (:t)", ['t'=>$content]);
    }
    if (Config::AppMode() === 'DEV') {
      Utils::dirExists();
      $url = Utils::$baseLogPath . $fileName;
      file_put_contents($url, $content);
    }

  }

  static function format_interval(DateInterval $interval) {
    $result = "";
    if ($interval->y) { $result .= $interval->format("%y лет "); }
    if ($interval->m) { $result .= $interval->format("%m месяцев "); }
    if ($interval->d) { $result .= $interval->format("%d дней "); }
    // if ($interval->h) { $result .= $interval->format("%h часов "); }
    // if ($interval->i) { $result .= $interval->format("%i минут "); }
    // if ($interval->s) { $result .= $interval->format("%s секунд "); }
    if(!$result) $result.="0 дней";
    return $result;
}
}
