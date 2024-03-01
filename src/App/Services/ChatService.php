<?php

declare (strict_types = 1);

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

    public function createChat(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
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
        $chat = $update['message']['chat'];
        $rules = $this->getChat($chat['id']);
        if (!$rules['rules']) {
            return;
        }

        $telegram->sendMessage($rules['rules'], $chat['id']);
    }

    public function updateChatRules(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $message = $update['message']['text'];
        $rules = substr($message, strlen("/rules_add "));
        $res = $this->updateChatDb($chat['id'], 'rules', $rules);
        $message = "Новые правила - {$res['rules']}";
        $telegram->sendMessage($message, $chat['id']);
    }

    public function updateChatGreet(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $message = $update['message']['text'];
        $greet_message = substr($message, strlen("/greet_add "));
        $res = $this->updateChatDb($chat['id'], 'greet_message', $greet_message);
        $message = "Новое приветствие - {$res['greet_message']}";
        $telegram->sendMessage($message, $chat['id']);
    }

    public function updateChatLeave(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $message = $update['message']['text'];
        $leave_message = substr($message, strlen("/leave_add "));
        $res = $this->updateChatDb($chat['id'], 'leave_message', $leave_message);
        $message = "Новое прощание - {$res['leave_message']}";
        $telegram->sendMessage($message, $chat['id']);
    }

    public function onChatEnter(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $profile = $update['message']['from'];
        if ($profile['is_bot']) {
            return;
        }

        $chatData = $this->getChat($chat['id']);
        if (!$chatData || !$chatData['greet_message']) {
            return;
        }

        $messsage = "{$profile['first_name']} {$chatData['greet_message']}";
        $telegram->sendMessage($messsage, $chat['id']);
    }

    public function onChatLeave(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $profile = $update['message']['from'];
        if ($profile['is_bot']) {
            return;
        }

        $chatData = $this->getChat($chat['id']);
        if (!$chatData || !$chatData['leave_message']) {
            return;
        }

        $messsage = "{$profile['first_name']} {$chatData['leave_message']}";
        $telegram->sendMessage($messsage, $chat['id']);
    }

    public function createChatUser(mixed $update)
    {
        $chat = $update['message']['chat'];
        $profile = $update['message']['from'];
        if ($profile['is_bot']) {
            return;
        }

        $query = "SELECT * from chat_participants WHERE chat_id={$chat['id']} AND user_id={$profile['id']}";
        $data = $this->db->query($query)->find();

        if (!$data) {
            $query = "INSERT INTO chat_participants (chat_id, user_id, username, first_name, last_name) VALUES (:chat_id, :user_id, :username, :first_name, :last_name)";
            $this->db->query($query, [
                'chat_id' => $chat['id'],
                'user_id' => $profile['id'],
                'username' => $profile['username'],
                'first_name' => iconv('UTF-8', 'UTF-8', $profile['first_name']),
                'last_name' => $profile['last_name'] ?? '',
            ]);

        }
    }

    public function updMsgCount($update)
    {
        $chat = $update['message']['chat'];
        $profile = $update['message']['from'];
        if ($profile['is_bot']) {
            return;
        }

        $user = $this->db->query("SELECT * FROM chat_participants WHERE user_id={$profile['id']}
    AND chat_id={$chat['id']}")->find();
        if (!$user) {
            return;
        }

        $query = "UPDATE chat_participants
    SET msg_count=msg_count+1
    WHERE user_id={$profile['id']}
    AND chat_id={$chat['id']}";
        $this->db->query($query);
    }

    public function msgStat(mixed $update, Telegram $telegram)
    {
        $chat = $update['message']['chat'];
        $query = "SELECT * FROM chat_participants WHERE chat_id={$chat['id']}
    ORDER BY msg_count DESC
    LIMIT 20
    ";
        $data = $this->db->query($query)->findAll();
        // var_dump($data);
        $msg = $this->renderMsgStat($data);
        $telegram->sendMessage($msg, $chat['id'], ['parse_mode' => 'HTML', "disable_notification" => true]);
    }

    private function renderMsgStat(mixed $data)
    {
        $res = '';
        $total = 0;
        foreach ($data as $user) {
            $id = $user['user_id'];
            $name = $user['first_name'];
            $userLink = "✅  <b><a href='https://t.me/user?id={$id}'>{$name}</a></b>";
            $res .= "{$userLink} - {$user['msg_count']} " . "\r\n";
            $total += $user['msg_count'];
        }

        return "👀Всего сообщений:  {$total} \r\n \r\n{$res}";
    }

    public function info(mixed $update, Telegram $telegram)
    {
        $msgText = $update['message']['text'];
        // $msgText = \str_replace('инфа',  '',$msgText);
        $msgText = \explode(' ', $msgText);
        array_shift($msgText);
        $msgText = implode(" ", $msgText);

        $chatId = $update['message']['chat']['id'];
        if (\strlen($msgText) === 0) {
            return;
        }
        $chance = rand(0, 100);
        $from = $update['message']["from"];

        $msgText = "<a href='tg://user?id={$from['id']}'>{$from['first_name']}</a>, вероятность, что $msgText - $chance%";

        $telegram->sendMessage($msgText, $chatId);
    }

    public function who(mixed $update, Telegram $telegram)
    {
        $msgText = $update['message']['text'];
        $msgText = \explode(' ', $msgText);
        array_shift($msgText);
        $msgText = implode(" ", $msgText);

        $chatId = $update['message']['chat']['id'];
        if (\strlen($msgText) === 0) {
            return;
        }
        $from = $update['message']["from"];
        $participants = $this->db->query("SELECT * from chat_participants WHERE chat_id=$chatId")->findAll();

        $chosenOne = $participants[array_rand($participants)];

        $msgText = "<b><a href='tg://user?id={$from['id']}'>{$from['first_name']}</a></b>, похоже что <b><a href='tg://user?id={$chosenOne['user_id']}'>{$chosenOne['first_name']}</a></b> <blockquote>$msgText</blockquote>";

        $telegram->sendMessage($msgText, $chatId);
    }

    public function when(mixed $update, Telegram $telegram)
    {
        $msgText = $update['message']['text'];
        $msgText = \explode(' ', $msgText);
        array_shift($msgText);
        $msgText = implode(" ", $msgText);

        $chatId = $update['message']['chat']['id'];
        if (\strlen($msgText) === 0) {
            return;
        }
        $from = $update['message']["from"];
        $minVal = time();
        $maxVal = time() + 31536 * 5 * 1000;
        $randTimeStamp = rand($minVal, $maxVal);
        $randDate = date('d-m-Y', $randTimeStamp);

        $msgText = "<b><a href='tg://user?id={$from['id']}'>{$from['first_name']}</a></b>,  <blockquote>$msgText</blockquote>
        произойдет $randDate";

        $telegram->sendMessage($msgText, $chatId);
    }

    //TODO: quotes from  chat
    public function createQuote(mixed $update, Telegram $telegram)
    {
        // chat_id,user_id,text
        if (!key_exists("reply_to_message", $update['message'])) {
            return;
        }

        $chatId = $update['message']['chat']['id'];
        $userId = $update['message']['reply_to_message']['from']['id'];
        $text = $update['message']['reply_to_message']['text'];

        $query = "INSERT INTO quotes (chat_id, user_id, text) VALUES(:chat_id, :user_id, :text)";
        $resId = $this->db->query($query, ['chat_id' => $chatId,
            'user_id' => $userId, 'text' => $text])->id();

        $quoteStr = $this->quoteString($resId);
        $telegram->sendMessage("цитата создана✍️\n" . $quoteStr, $chatId);
    }

    public function showQuotes(mixed $update, Telegram $telegram)
    {
        $isCbQuery = key_exists('callback_query', $update);
        $singleUserMode = false;
        $offset = 0;

        $randomQuote = false;
        $isForwardKey = true;
        $userId = $update['message']['reply_to_message']['from']['id'];
        if (key_exists("reply_to_message", $update['message'] ?? [])) {

            $singleUserMode = true;
        }

        if ($isCbQuery) {
            $offset = explode(':', $update['callback_query']['data'])[1] ?? 1;
            $isForwardKey = explode(':', $update['callback_query']['data'])[2] ?? false;
            $isForwardKey = $isForwardKey === 'F';
            $randomQuote = str_contains($update['callback_query']['data'], 'random');
            $update = $update['callback_query'];

            $userId = $update['from']['id'];
        }
        if(!$isCbQuery) {
            $randomQuote = true;
        }

        $chatId = $update['message']['chat']['id'];

        $userFilter = $singleUserMode ? " AND user_id=$userId" : '';
        $query = "SELECT id FROM quotes WHERE chat_id=$chatId
        $userFilter
        ORDER BY id LIMIT 1 OFFSET $offset";
        if ($randomQuote) {
            $query = "SELECT id
                  FROM quotes
                  WHERE chat_id=$chatId
                  $userFilter
                  ORDER BY RAND()
                  LIMIT 1;";
        }


        $data = $this->db->query($query)->find();
        if(!$data)return;

        $quoteStr = $this->quoteString($data['id']);

        $prevOffset = $offset === 0 ? 0 : $offset - 1;
        $nextOffset = $offset + 1;
        $keyboard = ['inline_keyboard' => [
            [
                ['text' => '◀️назад', "callback_data" => "quotes:$prevOffset:B"],
                ['text' => 'вперёд▶️', "callback_data" => "quotes:$nextOffset:F"],
            ], [
                ['text' => 'случайная цитата', "callback_data" => "quotesrandom"],
            ],
        ]];

        if ($isCbQuery) {
            $telegram->editMessageText($quoteStr, ['chat_id' => $chatId, 'parse_mode' => 'HTML',
                'message_id' => $update['message']['message_id']], $keyboard);
            return;
        }

        $telegram->sendMessage($quoteStr, $chatId, [], $keyboard);
    }

    private function quoteString(int | string $id)
    {
        $quote = $this->db->query("SELECT * FROM quotes where id=$id")->find();

        $user = $this->db->query("SELECT * FROM chat_participants WHERE user_id=:user_id", ['user_id' => $quote['user_id']])->find();
        $userLink = "<a href='tg://user?id={$user['user_id']}'>{$user['first_name']}</a>";
        $quoteString = "<blockquote>{$quote['text']}</blockquote>$userLink  {$quote['created_at']} 🗄";

        return $quoteString;
    }
    //TODO: x OR y
}
