<?php

declare (strict_types = 1);

namespace App\Services;

use App\Db\Db;
use App\Telegram\Telegram;
use App\Utils\Utils;
use Datetime;

class NfService
{

    public function __construct(private Db $db)
    {
    }

    private function getParticipant(int $id)
    {
        $query = "SELECT * FROM nfs WHERE user_id = $id";
        $data = $this->db->query($query)->find();
        return $data;
    }

    private function participantInfo(mixed $update)
    {
        $chatId = $update['message']['chat']['id'];
        $fromId = $update['message']["from"]['id'];
        $data = $this->getParticipant($fromId);
        return $data;
    }

    public function nfInfo($update, Telegram $telegram)
    {
        $chatId = $update['message']['chat']['id'];
        $fromId = $update['message']["from"]['id'];
        // get and send NF table
        $nfTable = $this->nfTable();
        $telegram->sendMessage($nfTable, $chatId,["reply_to_message_id" => $update['message']['message_id'],
        'parse_mode' => 'HTML']);
        // send user NF personal msg
        $profile = $this->participantInfo($update);
        if (!$profile) {
            $msg = 'У вас еще нет профиля, зарегестрируйтесь через сообщение: <blockquote><b>setnf XX-XX-XXXX, (Д-М-ГОД)</b></blockquote>';
            $telegram->sendMessage($msg, $chatId, ["reply_to_message_id" => $update['message']['message_id'],
                'parse_mode' => 'HTML']);
        }else {
          $telegram->sendMessage("{$this->profileString($update)}", $chatId, [
            "reply_to_message_id" => $update['message']['message_id'],
            'parse_mode' => 'HTML',
        ]);
        }
    }

    private function nfTable()
    {
        $query = "SELECT nfs.user_id, nfs.fails, nfs.nf_timer, chats.name AS chat_name, chat_participants.username, chat_participants.first_name
        from nfs
        INNER JOIN chats
        ON nfs.chat_id=chats.chat_id
        INNER JOIN chat_participants
        ON nfs.user_id=chat_participants.user_id AND nfs.chat_id=chat_participants.chat_id
        ORDER BY nf_timer ASC LIMIT 10";
        ;
        $data = $this->db->query($query)->findAll();
        $msg = "Таблица лидеров:\n\n";

        foreach ($data as $participant) {
          $id = $participant['user_id'];
          $name = $participant['first_name'];
          $nfTime = $participant['nf_timer'];
          $fails = $participant['fails'];
          $chatName = $participant['chat_name'];
          $userLink = "✅  <b><a href='https://t.me/user?id={$id}'>{$name}</a></b>";
          $diff = new Datetime($nfTime);
          $diff= $diff->diff(new Datetime());
          $nfTime = Utils::format_interval($diff);


          $msg .= "{$userLink} - <b>{$nfTime} | сбросов таймера - {$fails}</b>" . "\r\n";

      }
      return $msg;
    }

    private function convertDate(string $input)
    {
        $timestamp = strtotime($input);
    }

    public function setNf($update, Telegram $telegram)
    {
        // check if data is correct
        $chatId = $update['message']['chat']['id'];
        $fromId = $update['message']["from"]['id'];
        $userInput = $update['message']['text'];
        $userInput = explode(" ", $userInput)[1];
        $timestamp = strtotime($userInput);

        if (!$timestamp) {
            $telegram->sendMessage("invalid time format, pls give me:\n <blockquote><b>setnf XX-XX-XXXX - (Д-М-ГОД)</b></blockquote>", $chatId, [
                "reply_to_message_id" => $update['message']['message_id'],
                'parse_mode' => 'HTML',
            ]);
            return;
        };

        $mysqlDateTime = date("Y-m-d H:i:s", $timestamp);
        // get profile from DB. if not - create.
        $profile = $this->participantInfo($update);
        if (!$profile) {
            print('here');
            $query = "INSERT into nfs (user_id,chat_id,nf_timer) VALUES (:user_id,:chat_id,:nf_timer)";
            $resId = $this->db->query($query, ['user_id' => $fromId, 'chat_id' => $chatId, 'nf_timer' => $mysqlDateTime])->id();

            $telegram->sendMessage("{$this->profileString($update)}\nдата обновлена на:\n <blockquote><b>{$userInput}</b></blockquote>", $chatId, [
                "reply_to_message_id" => $update['message']['message_id'],
                'parse_mode' => 'HTML',
            ]);

        } else {
            // update profile with new data
            $query = "UPDATE nfs
             SET nf_timer='{$mysqlDateTime}',
             fails=fails+1
             WHERE user_id={$fromId}";
            $this->db->query($query);

            $telegram->sendMessage("{$this->profileString($update)}\nДата обновлена на:<blockquote><b>{$userInput}</b></blockquote>", $chatId, [
                "reply_to_message_id" => $update['message']['message_id'],
                'parse_mode' => 'HTML',
            ]);
        };

    }

    private function profileString($update)
    {
        $profile = $this->participantInfo($update);
        if (!$profile) {
            return '';
        }

        $date = substr($profile['nf_timer'], 0, 10);
        return "📗 Ваш профиль\n\n📌 Время начала: {$date}\n\n📌 Сбросов таймера: {$profile['fails']}";
    }
}
