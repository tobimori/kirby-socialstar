<?php

namespace tobimori\SocialStar\YouTube;

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use tobimori\SocialStar\SocialStar;

class Api
{
	protected static $instance;
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new self(
				apiKey: SocialStar::option('youtube.apiKey'),
			);
		}

		return self::$instance;
	}

	protected function __clone() {}
	protected function __construct(
		protected string $apiKey,
	) {}

	public function request(string $method, string $path, array $data = []): array|null
	{
		$url = "https://www.googleapis.com/youtube/v3/{$path}";

		$data['key'] = $this->apiKey;
		if ($method === 'GET') {
			$url .= '?' . http_build_query($data);
		}

		$response = Remote::request($url, [
			'method' => $method,
			'data' => $method !== 'GET' ? $data : null,
			'headers' => [
				'Accept' => 'application/json',
				'User-Agent' => SocialStar::userAgent()
			],
		]);

		if ($response->json() === null || $response->code() > 400) {
			throw new Exception([
				'message' => $response?->json()['error_message'] ?? $response->content(),
			]);
		}

		return $response->json();
	}

	public function getAccountIds(string $username): array
	{
		return $this->request(
			'GET',
			path: 'channels',
			data: [
				'part' => 'contentDetails',
				'forHandle' => $username,
			],
		);
	}

	public function getAccountDetails(string $id): array
	{
		return $this->request(
			'GET',
			path: "channels",
			data: [
				'id' => $id,
				'part' => A::join(['snippet', 'statistics'], ','),
			],
		);
	}

	public function getPlaylistItems(string $playlistId, string|null $pageToken = null): array
	{
		return $this->request(
			'GET',
			path: "playlistItems",
			data: [
				'part' => 'snippet',
				'playlistId' => $playlistId,
				'maxResults' => 20,
				'pageToken' => $pageToken,
			],
		);
	}

	public function cache(string $type, ...$args): array
	{
		$cache = App::instance()->cache('tobimori.socialstar');
		$key = $type . hash('md5', json_encode($args), true);

		return $cache->getOrSet($key, function () use ($type, $args) {
			return $this->$type(...$args);
		}, SocialStar::option('ttl'));
	}

	public function flushCache(): bool
	{
		return App::instance()->cache('tobimori.socialstar')->flush();
	}
}
