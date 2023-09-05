<?php

declare(strict_types=1);

namespace Services;

use Telegram\Telegram;
use Utils\Utils;


class VkGroupService
{

  public function __construct(private string $groupId, protected string $keyname, private string $filter = '', private bool $ignorePinned = false, private bool $showAlbums = false)
  {
  }

  public function getWallPost(int|string $offset = 0)
  {
    $apiKey = getenv('VK_API_KEY');
    $groupId = $this->groupId;
    $query = http_build_query([
      'access_token' => $apiKey, 'owner_id' => $groupId, 'offset' => $offset, 'count' => 1,
      'extended' => true, 'v' => '5.131'
    ]);
    $baseUrl = "https://api.vk.com/method/wall.get?" . $query;
    $data = json_decode(file_get_contents($baseUrl), true);
    Utils::writeLog('vkapiLog.json', $baseUrl);
    $groupName = $data['response']['groups'][0]['name'];

    $post = $data['response']['items'][0];
    $authorString = '';
    foreach ($data['response']['profiles'] as $profile) {
      if ($profile['id'] === $post['from_id']) {
        $authorString = "{$profile['first_name']} {$profile['last_name']} {$profile['screen_name']}";
      }
    }

    $postImage = [];

    foreach ($post['attachments'] as $attachment) {
      if ($attachment['type'] === 'photo') {
        $photo = end($attachment['photo']['sizes']);
        array_push($postImage,  $photo['url']);
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

    return  [$msg, $postImage, array_key_exists('is_pinned', $post), $groupName];
  }

  public function getPostHandler(mixed $update, Telegram $telegram)
  {
    $replyTo = null;
    $Offset = 0;
    $isCbQuery = key_exists('callback_query', $update);
    $message = $isCbQuery ? $update['callback_query']['message'] :
      $update['message'];
    $replyTo = $message['message_id'];
    $isForwardKey = true;

    if ($isCbQuery) {
      $Offset = explode(':', $update['callback_query']['data'])[1];
      $isForwardKey =  explode(':', $update['callback_query']['data'])[2] === 'F';
      $update = $update['callback_query'];
      $from = $update['from'];

      $whoPressed = "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a> нажал на кнопку"
        .  "\r\n" . "\r\n";;

      $telegram->editMessageText($whoPressed . "Just a second 'Mon, da Zul be working on dat task right now", [
        'chat_id' => $message['chat']['id'],
        'message_id' => $message['message_id'],
        'parse_mode' => 'HTML',
      ]);

      sleep(1);
    }



    $post = $this->getWallPost($Offset);
    $filter = $this->filter;
    $ignorePinned = $this->ignorePinned;

    if ($filter && !str_contains($post[0],  $filter) ||  $ignorePinned && $post[2]) {
      $filterSuccess = false;
      while (!$filterSuccess) {
        $Offset = $isForwardKey ? $Offset + 1  : $Offset - 1;
        if ($Offset < 0) {
          $Offset = 0;
          $isForwardKey = true;
        }
        $newPost = $this->getWallPost($Offset);
        $post = $newPost;
        $filterSuccess = str_contains($newPost[0],  $filter);
      }
    }

    $chatId = $message['chat']['id'];
    $nextOffset = $Offset + 1;
    $prevOffset = $Offset == 1 ? 1 : $Offset - 1;
    $keyname = $this->keyname;

    $keyboard = ['inline_keyboard' => [
      [
        ['text' => '◀️назад', "callback_data" => "$keyname:$prevOffset:B"],
        ['text' => 'вперёд▶️', "callback_data" => "$keyname:$nextOffset:F"]
      ], [['text' => 'в начало', "callback_data" => "$keyname:1"]]
    ]];

    [$msg, $image] = $post;

    $imageLink =  $image[0] ? "<a href='$image[0]'>^_^</> \r\n" : '';
    $msg = $imageLink . $msg;

    if ($isCbQuery) {

      if ($this->showAlbums && count($image) > 1) {
        $this->sendImagesGroup($update, $telegram, $image, $keyboard, $post[3], $post[0]);
        return;
      }

      $telegram->editMessageText($msg, [
        'chat_id' => $chatId, 'message_id' => $message['message_id'],
        'parse_mode' => 'HTML',
      ], $keyboard);
      return;
    }

    if ($this->showAlbums &&  count($image) > 1) {
      $this->sendImagesGroup($update, $telegram, $image, $keyboard, $post[3], $post[0]);
      return;
    }

    $telegram->sendMessage($msg, $chatId, ["reply_to_message_id" => $replyTo, "parse_mode" => "HTML"], $keyboard);
  }

  public function sendImagesGroup(mixed $update, Telegram $telegram, array $images, array $keyboard = [], string $groupName, string $msg =  '')
  {
    $chatId = $update['message']['chat']['id'];
    $mediaArray = [];
    $msg =  strip_tags($msg);
    foreach ($images as $image) {
      $res = ['type' => 'photo', 'media' =>  $image];
      array_push($mediaArray, $res);
    }

    // $mediaArray[0]['caption']  = mb_convert_encoding(substr( $msg, 0, 499), 'UTF-8') ;

    // $mediaArray[0]['parse_mode']  = 'HTML';



    $params = ['chat_id' => $chatId, 'media' => json_encode($mediaArray)];
    $telegram->sendMediaGroup($params);
    if (count($keyboard)) {
      $telegram->sendMessage($msg, $chatId, ["parse_mode" => "HTML"], $keyboard);
    }
  }
}
