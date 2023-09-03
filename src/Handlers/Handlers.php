<?php

declare(strict_types=1);

namespace Handlers;

use Services\CommonService;
use Services\JokeService;
use Services\VkGroupService;
use Services\WeatherService;
use Telegram\Telegram;


class  Handlers{
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
    $service = new VkGroupService('-196285812', 'vk_next_game', '#FREE');
    $service->getPostHandler($update, $telegram);
  }

  function sfPostHandler(mixed $update, Telegram $telegram)
  {
    $service = new VkGroupService(getenv('VK_GRP'), 'vk_next_post', '', true);
    $service->getPostHandler($update, $telegram);
  }
  function lrgPostHandler(mixed $update, Telegram $telegram)
  {
    $service = new VkGroupService('-142730744', 'lrg_next_post', '#от_подписчицы', showAlbums:true);
    $service->getPostHandler($update, $telegram);
  }
}

