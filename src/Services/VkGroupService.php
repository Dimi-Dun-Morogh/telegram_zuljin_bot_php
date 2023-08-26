<?php

declare(strict_types=1);

namespace Services;

use Telegram\Telegram;
use Utils\Utils;


class VkGroupService
{

  public function __construct(private string $groupId, private string $keyname)
  {
  }

  public function getWallPost(int|string $offset = 1)
  {
    $apiKey = getenv('VK_API_KEY');
    $groupId = $this->groupId;
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
    $isCbQuery = false;

    if (key_exists('callback_query', $update)) {
      $isCbQuery = true;
      $Offset = explode(':', $update['callback_query']['data'])[1];
      $update = $update['callback_query'];
      $from = $update['from'];
      $whoPressed = "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a> нажал на кнопку"
        .  "\r\n" . "\r\n";;

      $telegram->editMessageText($whoPressed . "Just a second 'Mon, da Zul be working on dat task right now", [
        'chat_id' => $update['message']['chat']['id'],
        'message_id' => $update['message']['message_id'],
        'parse_mode' => 'HTML',
      ]);

      sleep(1);
    } else {
      $replyTo = $update['message']['message_id'];
    }


    $chatId = $update['message']['chat']['id'];
    $nextOffset = $Offset + 1;
    $prevOffset = $Offset == 1? 1 : $Offset - 1;
    $keyname = $this->keyname;

    $keyboard = ['inline_keyboard' => [
      [
        ['text' => '◀️назад', "callback_data" => "$keyname:$prevOffset"], ['text' => $Offset, "callback_data" => 'null'],
        ['text' => 'вперёд▶️', "callback_data" => "$keyname:$nextOffset"]
      ], [['text' => 'в начало', "callback_data" => "$keyname:1"]]
    ]];
    [$msg, $image] = $this->getWallPost($Offset);



    if ($isCbQuery) {
      if ($image) {
        $imageLink = "<a href='$image'>^_^</>";
        $msg = "$imageLink \r\n" .  $msg;
      }
      $telegram->editMessageText($msg, [
        'chat_id' => $chatId, 'message_id' => $update['message']['message_id'],
        'parse_mode' => 'HTML',
      ], $keyboard);

      return;
    }


    if ($image) {
      $imageLink = "<a href='$image'>^_^</>";
      $telegram->sendMessage("$imageLink \r\n" .  $msg, $chatId, ["reply_to_message_id" => $replyTo, "parse_mode" => "HTML"], $keyboard);

      return;
    }

    $telegram->sendMessage($msg, $chatId, ["reply_to_message_id" => $replyTo, "parse_mode" => "HTML"], $keyboard);
  }
}
