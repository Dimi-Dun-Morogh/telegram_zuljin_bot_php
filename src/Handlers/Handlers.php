<?php

declare(strict_types=1);

namespace Handlers;

use Services\CommonService;
use Services\JokeService;
use Services\VkGroupService;
use Services\WeatherService;
use Telegram\Telegram;


class  Handlers
{
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
}
