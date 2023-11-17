<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Services\ChatService;
use App\Services\CommonService;
use App\Services\JokeService;
use App\Services\VkGroupService;
use App\Services\WeatherService;
use App\Telegram\Telegram;
use App\Db\Db;
use App\Services\RandTvjService\RandTvjService;

class  Handlers
{
  public function __construct(private Db $db)
  {
  }

  private function isAdmin(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $userId = $update['message']['from']['id'];
    $userStats = $telegram->getChatMember($chat['id'], $userId);
    if ($userStats['result']['status'] === 'member') {
      $telegram->sendMessage("Эта комманда только для админов", $chat['id']);
      return false;
    }
    return true;
  }

  function jokesHandler(mixed $update, Telegram $telegram)
  {
    $jokeService = new JokeService();
    $jokeService->jokesHandler($update, $telegram);
  }

  function helpHandler(mixed $update, Telegram $telegram)
  {
    $service = new CommonService();
    $service->helpHandler($update, $telegram);
  }

  function weatherHandler(mixed $update, Telegram $telegram)
  {
    $service = new WeatherService();
    $service->weatherHandler($update, $telegram);
  }

  function gamesPostHandler(mixed $update, Telegram $telegram)
  {

    $service = new VkGroupService(['groupId' => '-196285812', 'keyname' => 'vk_next_game', 'filter' => '#FREE']);
    $service->getPostHandler($update, $telegram);
  }

  function sfPostHandler(mixed $update, Telegram $telegram)
  {
    $service = new VkGroupService(['groupId' => getenv('VK_GRP'), 'keyname' => 'vk_next_post', 'ignorePinned' => true]);
    $service->getPostHandler($update, $telegram);
  }
  function lrgPostHandler(mixed $update, Telegram $telegram)
  {
    $service = new VkGroupService(['groupId' => '-142730744', 'keyname' => 'lrg_next_post', 'filter' => '#от_подписчицы', 'showAlbums' => true]);
    $service->getPostHandler($update, $telegram);
  }

  function memsPostHandler(mixed $update, Telegram $telegram)
  {
    $service = new VkGroupService(['groupId' => '-57846937', 'keyname' => 'mem_next_post',  'showAlbums' => true, 'ignorePinned' => true, 'onlyImage' => true]);
    $service->getPostHandler($update, $telegram);
  }

  function startHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->createChat($update, $telegram);
    $this->helpHandler($update, $telegram);
  }

  function updateRulesHandler(mixed $update, Telegram $telegram)
  {
    if (!$this->isAdmin($update, $telegram)) return;
    $service = new ChatService($this->db);
    $service->updateChatRules($update, $telegram);
  }

  function updateGreetHandler(mixed $update, Telegram $telegram)
  {
    if (!$this->isAdmin($update, $telegram)) return;
    $service = new ChatService($this->db);
    $service->updateChatGreet($update, $telegram);
  }

  function updateLeaveHandler(mixed $update, Telegram $telegram)
  {
    if (!$this->isAdmin($update, $telegram)) return;
    $service = new ChatService($this->db);
    $service->updateChatLeave($update, $telegram);
  }

  function onChatEnterHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->onChatEnter($update, $telegram);
    $service->showRules($update, $telegram);
  }

  function onChatLeaveHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->onChatLeave($update, $telegram);
  }


  function onEachMessageHandler(mixed $update, Telegram $telegram){
    if(key_exists('callback_query', $update)) return;

    $service = new ChatService($this->db);
    //!creat db is there is not any
    $service->createChat($update, $telegram);
    $service->createChatUser($update);

    $service->updMsgCount($update);
  }

  function msgStatHandler(mixed $update, Telegram $telegram) {
    $service = new ChatService($this->db);
    $service->msgStat($update, $telegram);
  }

  function tvjHandler(mixed $update, Telegram $telegram)
  {
    $service = new RandTvjService($this->db);
    $service->handleRandomLine($update, $telegram);
  }

  function infoHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->info($update, $telegram);
  }

  function whoHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->who($update, $telegram);
  }

  function whenHandler(mixed $update, Telegram $telegram)
  {
    $service = new ChatService($this->db);
    $service->when($update, $telegram);
  }
}
