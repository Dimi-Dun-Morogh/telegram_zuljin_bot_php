<?php

declare(strict_types=1);

namespace Services;

use Telegram\Telegram;

function escapeMarkdown($string)
{
  $markdownChars = array('*', '_', '~', '', '|', '-', ',', '.');
  $escapedChars = array("\*", "\_", "\~", "\\", "\|", '\-', '\,', '\.');
  return str_replace($markdownChars, $escapedChars, $string);
}


class VkGroupService
{

  public function getWallPost()
  {
    $apiKey = $_ENV['VK_API_KEY'];
    $groupId = $_ENV['VK_GRP'];
    $query = http_build_query([
      'access_token' => $apiKey, 'owner_id' => $groupId, 'count' => 2,
      'extended' => true, 'v' => '5.131'
    ]);
    $baseUrl = "https://api.vk.com/method/wall.get?" . $query;
    $data = json_decode(file_get_contents($baseUrl), true);

    $groupName = $data['response']['groups'][0]['name'];

    $post = $data['response']['items'][1];
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


    $msg = $groupName . '%0A' . '%0A'
      . "<b>" . $postText .  "</b>"  . '%0A'  . '%0A'
      . "<i>" . $authorString . "  " . $date_string . "</i>" . '%0A'  . '%0A'
      . "comments: " . $comments . " " . "likes: " . $likes .  '%0A';

    return  [$msg, $postImage];
  }

  public function getPostHandler(mixed $update, Telegram $telegram)
  {
    $replyTo = $update['message']['message_id'];
    $chatId = $update['message']['chat']['id'];

    [$msg, $image] = $this->getWallPost();

    if ($image) {

      $url = $telegram->baseUrl;

      file_put_contents(__DIR__ . '../../../log.txt',  $url);
      $telegram->sendPhoto($image, $msg, ['chat_id' => $chatId, 'parse_mode' => 'HTML']);

       return;
    }

    $telegram->sendMessage($msg, $chatId, ["reply_to_message_id" => $replyTo, "parse_mode" => "HTML"]);
  }
}
