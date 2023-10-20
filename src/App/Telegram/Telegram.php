<?php

declare (strict_types = 1);

namespace App\Telegram;

use App\Utils\Utils;

class Telegram {
	public string $baseUrl = 'https://api.telegram.org/bot';

	public function __construct(string $apiKey) {
		$this->baseUrl = $this->baseUrl . $apiKey;
	}

	private function api(string $method, array $params = [], array $keyboard = []) {
		$url = $this->baseUrl . "/" . $method;
		if (!empty($params)) {
			$url = $url . "?" . http_build_query($params);
		}

		if (!empty($keyboard)) {
			$keyboard = json_encode($keyboard);
			$url = $url . "&reply_markup=$keyboard";
		}

		$data = file_get_contents($url);
		Utils::writeLog('apiLog.json', $data);

		if ($data) {
			$data = json_decode($data, true);
		}

		if ($data === false) {
			// An error occurred, write error message to file
			$error = error_get_last();
			$message = $error['message'];
			Utils::writeLog('logerror.txt', $message);
		}

		return $data;
	}

	public function getUpdates($params = []) {
		$data = $this->api('getUpdates', $params);
		return $data;
	}

	private function cGet(string $url) {
		Utils::writeLog('log.txt', 'cGEt' . "\r\n" . $url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);

		curl_close($ch);
		if (!$res) {

			Utils::writeLog('logerror.txt', curl_error($ch) . "\n" . $url);
			echo 'Curl error: ' . curl_error($ch) . " " . "\n" . $url;
		}

		Utils::writeLog('apiLog.json', 'here' . $res);
		return $res;
	}

	public function sendMessage(string $message, string | int $chatId, array $params = [], array $keyboard = []) {
		$splitText = $this->splitMessage($message);

		$url = $url = $this->baseUrl . "/sendMessage?" . "&chat_id=$chatId&parse_mode=HTML&text=" . rawurlencode($splitText[0]) . "&"

		. http_build_query($params);

		if (count($splitText) > 1) {
			$this->cGet($url);
			$url = $url = $this->baseUrl . "/sendMessage?" . "&chat_id=$chatId&parse_mode=HTML&text=" . rawurlencode($splitText[1]) . "&"

			. http_build_query($params);
		}

		if (!empty($keyboard)) {
			$keyboard = urlencode(json_encode($keyboard));
			$url = $url . "&reply_markup=$keyboard";
		}

		$this->cGet($url);
	}

	public function getChatMember(int $chatId, int $userId) {
		return $this->api('getChatMember',['chat_id'=>$chatId, 'user_id'=>$userId]);
	}

	public function setWebHook(string $url) {
		return $this->api('setWebhook', ['url' => $url]);
	}

	public function deleteWebHook() {
		$this->api('deleteWebhook');
	}

	public function WebhookInfo() {
		return $this->api('getWebhookInfo?');
	}

	public function getMe() {
		return $this->api('getMe?');
	}

	public function getWebhookUpdate() {
		Utils::writeLog('hook.json', file_get_contents('php://input'));

		$update = json_decode(file_get_contents('php://input'), true);
		return $update;
	}

	public function answerCallbackQuery(int | string $id, array $params = []) {
		$this->api('answerCallbackQuery', array_merge(['callback_query_id' => $id], $params));
	}

	public function sendPhoto(string $imgUrl, string $captions = '', array $params = [], array $keyboard = []) {

		$captions = rawurlencode($captions);

		$url = $this->baseUrl . "/sendPhoto?" . "&photo=" . urlencode($imgUrl)
		. "&caption=" . $captions . "&" . http_build_query($params);

		if (!empty($keyboard)) {
			$keyboard = json_encode($keyboard);

			$url = $url . "&&reply_markup=$keyboard";
		}
		$this->cGet($url);
	}

	public function editMessageText(string $text, array $params, array $keyboard = []) {
		$splitText = $this->splitMessage($text);
		$url = $this->baseUrl . "/editMessageText?" . http_build_query($params)
		. "&text=" . rawurlencode($splitText[0]);

		if (count($splitText) > 1) {
			//send 1st message and change url;
			$this->cGet($url);
			$url = $this->baseUrl . "/sendMessage?" . http_build_query($params)
			. "&text=" . rawurlencode($splitText[1]);
		}

		if (!empty($keyboard)) {
			$keyboard = urlencode(json_encode($keyboard));

			$url = $url . "&&reply_markup=$keyboard";
		}

		$this->cGet($url);
	}
	private function splitMessage(string $text) {
		$res = [];
		$res[] = substr($text, 0, 3900);
		if (strlen($text) > 3900) {
			// ! проблема с незакрытыми хтмл тегами
			$text = strip_tags($text);
			$res[0] = substr($text, 0, 3900) . "\r\n" . "⬇️";
			$res[1] = "\r\n" . "⬆️" . substr($text, 3900);
		}
		$res = array_map(fn($el) => mb_convert_encoding($el, "UTF-8"), $res);
		return $res;
	}
	/**
	 * https://core.telegram.org/bots/api#sendmediagroup
	 */
	public function sendMediaGroup(array $params) {

		$url = $this->baseUrl . "/sendMediaGroup?" . http_build_query($params);
		$this->cGet($url);
	}

	/**
	 * https://core.telegram.org/bots/api#inputmedia
	 */
	public function editMessageMedia(array $params, array $keyboard = []) {
		$url = $this->baseUrl . "/editMessageMedia?" . http_build_query($params);
		if (!empty($keyboard)) {
			$keyboard = json_encode($keyboard);

			$url = $url . "&reply_markup=$keyboard";
		}
		Utils::writeLog('log.txt', $url);
		$this->cGet($url);
	}
	public function deleteMessage(string | int $chatId, string | int $messageId) {
		$url = $this->baseUrl . "/deleteMessage?chat_id=" . $chatId . "&message_id=" . $messageId;
		$this->cGet($url);
	}
}
