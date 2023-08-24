<?php

declare(strict_types=1);

namespace Services;

use Telegram\Telegram;
use Utils\Utils;


class VkGroupService
{

  public function getWallPost(int|string $offset = 1)
  {
    $apiKey = $_ENV['VK_API_KEY'];
    $groupId = $_ENV['VK_GRP'];
    $query = http_build_query([
      'access_token' => $apiKey, 'owner_id' => $groupId, 'offset' => $offset, 'count' => 1,
      'extended' => true, 'v' => '5.131'
    ]);
    $baseUrl = "https://api.vk.com/method/wall.get?" . $query;
    $data = json_decode(file_get_contents($baseUrl), true);

    $groupName = $data['response']['groups'][0]['name'];

    $post = $data['response']['items'][0];
    $authorString = '';
    foreach ($data['response']['profiles'] as $profile) {
      if ($profile['id'] === $post['from_id']) {
        $authorString = "{$profile['first_name']} {$profile['last_name']} {$profile['screen_name']}";
      }
    }

    $postImage = '';

    foreach ($post['attachments'] as $attachment) {
      if ($attachment['type'] === 'photo') {
        $photo = end($attachment['photo']['sizes']);
        $postImage = $photo['url'];
        break;
      }
    }

    $date_string = date('Y-m-d H:i:s', $post['date']);
    $postText =  $post['text'];
    $comments = $post['comments']['count'];
    $likes = $post['likes']['count'];


    $msg = $groupName . "\r\n"
      . "<b>" . $postText .  "</b>"  . "\r\n"  . "\r\n"
      . "<i>" . $authorString . "  " . $date_string . "</i>" . "\r\n"  . "\r\n"
      . "comments: " . $comments . " " . "likes: " . $likes .  "\r\n";

    return  [$msg, $postImage];
  }

  public function getPostHandler(mixed $update, Telegram $telegram)
  {
    $replyTo = null;
    $Offset = 1;

    if (key_exists('callback_query', $update)) {

      $Offset = explode(':', $update['callback_query']['data'])[1];
      $update = $update['callback_query'];
    } else {
      $replyTo = $update['message']['message_id'];
    }


    $chatId = $update['message']['chat']['id'];
    $nextOffset = $Offset + 1;
    $keyboard = ['inline_keyboard' => [
      [
        ['text' => 'следующий', "callback_data" => "vk_next_post:$nextOffset"]
      ]
    ]];
    [$msg, $image] = $this->getWallPost($Offset);

    if ($image) {
      $telegram->sendPhoto($image, $msg, ['chat_id' => $chatId, 'parse_mode' => 'HTML'], $keyboard);
      return;
    }

    $telegram->sendMessage($msg, $chatId, ["reply_to_message_id" => $replyTo, "parse_mode" => "HTML"], $keyboard);
  }
}
