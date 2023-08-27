<?php

namespace Services;

use DateInterval;
use DateTime;
use DateTimeZone;
use Telegram\Telegram;

//http://api.openweathermap.org/data/2.5/forecast?q=${encodeURIComponent(city)}&lang=ru&cnt=8&units=metric&appid=${config.weatherApiKey}

class WeatherService
{
  private string $baseUrl = "http://api.openweathermap.org/data/2.5/forecast";



  private function getWeather(string $city)
  {

    $apiKey = getenv('WEATHER_KEY');

    $params = http_build_query([
      'q' => $city, 'lang' => 'ru', 'cnt' => 8,
      'units' => 'metric', 'appid' => $apiKey
    ]);
    $url = $this->baseUrl . "?" . $params;
    echo $url;
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    if (!$response) {
      return "не могу найти погоду по данному запросу - {$city}";
    }
    return  $this->weatherStr($response);
  }


  public function weatherHandler(mixed $update, Telegram $telegram)
  {

    $city = $update['message']['text'];
    $city = explode(' ', $city)[1];
    if (!$city) return;

    $msg = $this->getWeather($city);
    $telegram->sendMessage($msg, $update['message']['chat']['id'], ['parse_mode' => 'HTML']);
  }

  private function weatherStr(mixed $data)
  {
    $weatherArr = $data['list'];
    $city = $data['city'];
    $msg = "ℹ️Погода в {$data['city']['name']} 👀" . "\r\n";
    foreach ($weatherArr as $wItem) {
      $date = explode(' ', $wItem['dt_txt'])[1];
      $date = substr($date, 0, 5);
      $temp =  round($wItem['main']['temp'], 1);
      $weather = $wItem['weather'][0];
      $emoji = $this->idxEmoji($weather['id']);
      $description  = $weather['description'] . " " . $emoji;

      $resStr = "\r\n" . "{$date}    {$temp} {$description} \r\n";
      $msg .= $resStr;
    }
    $dateStr = "\r\n☀️Восход " . $this->datexTimezone($city['sunrise'], $city['timezone'])
      . " 🌚Закат " . $this->datexTimezone($city['sunset'], $city['timezone']);
    $msg .= $dateStr;

    return "<b>" . $msg . "</b>";
  }


  private function datexTimezone(int $time, int $zone)
  {
    $date = new DateTime("@$time");
    $date->setTimezone(new DateTimeZone('UTC'));

    $date->add(new DateInterval("PT{$zone}S"));

    $time_string = $date->format('H:i');

    return $time_string;
  }

  private  function idxEmoji(string $id)
  {
    $emoji = '';
    // $id = (string) $id;
    // $id =  substr($id, 0, 1);
    switch ($id) {
      case ($id < 300):
        $emoji .= "⛈";
        break;
      case ($id >= 300 && $id < 400):
        $emoji .= "💧";
        break;
        case ($id >= 500 && $id < 600):
        $emoji .= "☔️";
        break;
        case ($id >= 600 && $id < 700):
        $emoji .= "❄️";
        break;
      case 800:
        $emoji .= "☀️";
        break;
        case ($id >= 801 && $id < 900):
          $emoji .= "☁️";
          break;
      default:
        break;
    }
    return $emoji;
  }
}
