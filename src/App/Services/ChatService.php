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

  public function showRules(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $rules = $this->getChat($chat['id']);
    if (!$rules['rules']) return;
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

  public function createChatUser(mixed $update)
  {
    $chat =  $update['message']['chat'];
    $profile = $update['message']['from'];
    if ($profile['is_bot']) return;

    $query = "SELECT * from chat_participants WHERE chat_id={$chat['id']} AND user_id={$profile['id']}";
    $data = $this->db->query($query)->find();


    if (!$data) {
      $query = "INSERT INTO chat_participants (chat_id, user_id, username, first_name, last_name) VALUES (:chat_id, :user_id, :username, :first_name, :last_name)";
      $this->db->query($query, [
        'chat_id' => $chat['id'],
        'user_id' => $profile['id'],
        'username' => $profile['username'],
        'first_name' => iconv('UTF-8', 'UTF-8', $profile['first_name']) ,
        'last_name' => $profile['last_name'] ?? ''
      ]);


    }
  }

  public function updMsgCount($update)
  {
    $chat =  $update['message']['chat'];
    $profile = $update['message']['from'];
    if ($profile['is_bot']) return;
    $user = $this->db->query("SELECT * FROM chat_participants WHERE user_id={$profile['id']}
    AND chat_id={$chat['id']}")->find();
    if (!$user) return;
    $query = "UPDATE chat_participants
    SET msg_count=msg_count+1
    WHERE user_id={$profile['id']}
    AND chat_id={$chat['id']}";
    $this->db->query($query);
  }

  public function msgStat(mixed $update, Telegram $telegram)
  {
    $chat =  $update['message']['chat'];
    $query = "SELECT * FROM chat_participants WHERE chat_id={$chat['id']}
    ORDER BY msg_count DESC
    LIMIT 20
    ";
    $data = $this->db->query($query)->findAll();
    // var_dump($data);
    $msg = $this->renderMsgStat($data);
    $telegram->sendMessage($msg, $chat['id'], ['parse_mode' => 'HTML', "disable_notification"=>true]);
  }

  private  function renderMsgStat(mixed $data) {
    $res =  '';
    $total = 0;
    foreach($data as $user) {
      $id = $user['user_id'];
      $name = $user['first_name'];
      $userLink = "✅  <b><a href='https://t.me/user?id={$id}'>{$name}</a></b>";
      $res .= "{$userLink} - {$user['msg_count']} " . "\r\n";
      $total += $user['msg_count'];
    }

    return "👀Всего сообщений:  {$total} \r\n \r\n{$res}";
  }
}
