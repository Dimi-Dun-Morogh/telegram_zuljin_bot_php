<?php

declare(strict_types=1);

namespace App\Services;

use App\Db\Db;
use App\Telegram\Telegram;

class ChatService
{
  public function __construct(private Db $db)
  {
  }
  public function chatExists(int $id)
  {

    $query = "SELECT * FROM chats WHERE chat_id=$id";
    $data = $this->db->query($query)->count();
    return (bool) $data;
  }

  private function createChatDb(int $id, string $cName)
  {
    $query = "INSERT into chats (`chat_id`, `name`) VALUES (:chat_id, :name)";
    $this->db->query($query, ['chat_id' => $id, 'name' => $cName]);
  }

  public function  createChat(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $exists = $this->chatExists($chat['id']);
    if (!$exists) {
      $chatName = $chat['type'] === 'private' ? $chat['first_name'] . " @" . $chat['username']
        : $chat['title'];
      $this->createChatDb($chat['id'], $chatName);
    }
  }

  public function getChat(int $id)
  {
    $query = "SELECT * from chats WHERE chat_id={$id}";
    $data = $this->db->query($query)->find();
    return $data;
  }

  private function updateChatDb(int $id, string $field, string $value)
  {
    $query = "UPDATE chats SET {$field}='{$value}'
    WHERE chat_id={$id}
    "; //!not safe

    $response = $this->db->query($query)->query("SELECT * FROM chats WHERE chat_id={$id}")->find();
    return $response;
  }

  public function showRules(mixed $update, Telegram $telegram) {
    $chat =  $update['message']['chat'];
    $rules = $this->getChat($chat['id']);
    if(!$rules['rules']) return;
    $telegram->sendMessage($rules['rules'], $chat['id']);
  }

  public function  updateChatRules(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $message = $update['message']['text'];
    $rules = substr($message, strlen("/rules_add "));
    $res = $this->updateChatDb($chat['id'], 'rules', $rules);
    $message = "Новые правила - {$res['rules']}";
    $telegram->sendMessage($message, $chat['id']);
  }

  public function  updateChatGreet(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $message = $update['message']['text'];
    $greet_message = substr($message, strlen("/greet_add "));
    $res = $this->updateChatDb($chat['id'], 'greet_message', $greet_message);
    $message = "Новое приветствие - {$res['greet_message']}";
    $telegram->sendMessage($message, $chat['id']);
  }

  public function  updateChatLeave(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $message = $update['message']['text'];
    $leave_message = substr($message, strlen("/leave_add "));
    $res = $this->updateChatDb($chat['id'], 'leave_message', $leave_message);
    $message = "Новое прощание - {$res['leave_message']}";
    $telegram->sendMessage($message, $chat['id']);
  }

  public function  onChatEnter(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $profile = $update['message']['from'];
    if ($profile['is_bot']) return;
    $chatData = $this->getChat($chat['id']);
    if (!$chatData || !$chatData['greet_message']) return;
    $messsage = "{$profile['first_name']} {$chatData['greet_message']}";
    $telegram->sendMessage($messsage, $chat['id']);
  }

  public function  onChatLeave(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $profile = $update['message']['from'];
    if ($profile['is_bot']) return;
    $chatData = $this->getChat($chat['id']);
    if (!$chatData || !$chatData['leave_message']) return;
    $messsage = "{$profile['first_name']} {$chatData['leave_message']}";
    $telegram->sendMessage($messsage, $chat['id']);
  }
}
