<?php

namespace tobimori\SocialStar\Instagram;

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
				appId: SocialStar::option('instagram.appId'),
				appSecret: SocialStar::option('instagram.appSecret')
			);
		}

		return self::$instance;
	}

	protected function __clone() {}
	protected function __construct(
		protected string $appId,
		protected string $appSecret,
	) {}


	public function getOAuthUrl(array $params = []): string
	{
		$base = 'https://www.instagram.com/oauth/authorize';
		$params = A::merge([
			'client_id' => $this->appId,
			'scope' => 'business_basic',
			'response_type' => 'code',
			'redirect_uri' => SocialStar::option('instagram.callbackUri'),
		], $params);

		return $base . '?' . http_build_query($params);
	}

	/**
	 * "access_token": "<THE_ACCESS_TOKEN>",
	 * "user_id": "<INSTAGRAM_APP_SCOPED_USER_ID>",
	 * "permissions": ["<LIST_OF_GRANTED_PERMISSIONS>"]
	 */
	public function getAccessToken(string $code): array
	{
		// this is not using the class request since it's a different api base url
		$response = Remote::post("https://api.instagram.com/oauth/access_token", [
			'data' => [
				'code' => $code,
				'client_id' => $this->appId,
				'client_secret' => $this->appSecret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => SocialStar::option('instagram.callbackUri')
			],
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'User-Agent' => SocialStar::userAgent()
			]
		])?->json();

		if (isset($response['error_message'])) {
			throw new Exception([
				'message' => $response['error_message'],
			]);
		}

		return $response;
	}

	public function request(string $method, string $path, array $data = [], bool $versionize = true): array|null
	{
		$url = "https://graph.instagram.com" . ($versionize ? "/v21.0" : "") . "/{$path}";

		if ($method === 'GET' && $data !== []) {
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

	public function getLongLivedAccessToken(string $accessToken): array
	{
		return $this->request(
			'GET',
			path: 'access_token',
			data: [
				'grant_type' => 'ig_exchange_token',
				'client_secret' => $this->appSecret,
				'access_token' => $accessToken,
			],
			versionize: false
		);
	}

	public function refreshLongLivedAccessToken(string $accessToken): array
	{
		return $this->request(
			'GET',
			path: 'refresh_access_token',
			data: [
				'grant_type' => 'ig_refresh_token',
				'refresh_token' => $accessToken,
			],
			versionize: false
		);
	}

	public function getUserDetails(string $accessToken, array $fields = [
		'id',
		'user_id',
		'username',
		'name',
		'profile_picture_url',
		'followers_count',
		'follows_count',
		'media_count'
	]): array
	{
		return $this->request(
			'GET',
			path: 'me',
			data: [
				'fields' => implode(',', $fields),
				'access_token' => $accessToken,
			]
		);
	}

	public function getUserMediaIds(string $accessToken, string $userId = 'me'): array
	{
		return $this->request(
			'GET',
			path: "{$userId}/media",
			data: [
				'access_token' => $accessToken,
			]
		);
	}


	public function getUserMedia(string $accessToken, string $mediaId, array $fields = [
		'id',
		'caption',
		'comments_count',
		'is_comment_enabled',
		'is_shared_to_feed',
		'like_count',
		'media_product_type',
		'media_type',
		'media_url',
		'permalink',
		'shortcode',
		'thumbnail_url',
		'timestamp',
	]): array
	{
		return $this->request(
			'GET',
			path: $mediaId,
			data: [
				'access_token' => $accessToken,
				'fields' => implode(',', $fields),
			]
		);
	}

	public function getUserMediaChildren(
		string $accessToken,
		string $mediaId,
		array $fields = [
			'id',
			'media_url',
			'media_type',
		]
	) {
		return $this->request(
			'GET',
			path: $mediaId . '/children',
			data: [
				'access_token' => $accessToken,
				'fields' => implode(',', $fields),
			]
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
