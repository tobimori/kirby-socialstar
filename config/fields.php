<?php

use Kirby\Data\Yaml;
use Kirby\Exception\Exception;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use tobimori\SocialStar\Instagram\Api as InstagramApi;
use tobimori\SocialStar\SocialStar;

return [
	'socialstar-instagram-connector' => [
		'computed' => [
			'hasAuthCredentials' => function () {
				return SocialStar::option('instagram.appId') !== null && SocialStar::option('instagram.appSecret') !== null;
			},
			'authUrl' => function () {
				return InstagramApi::instance()->getOAuthUrl(['state' => json_encode([
					'page' => $this->model()->uid(),
					'field' => $this->name()
				])]);
			},
			'userDetails' => function () {
				$value = Yaml::decode($this->value());
				if (!$value || !$value['token']) {
					return null;
				}

				$api = InstagramApi::instance();
				try {
					$data = $api->cache('getUserDetails', $value['token']);
					if ($data['user_id']) {
						return $data;
					}
				} catch (\Exception $e) {
				}

				return null;
			}
		],
		'api' => function () {
			return [
				[
					'pattern' => '/update',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							return false;
						}

						$page->instagramApi()->flushCache();
						$page->fetchNewInstagramPosts();
						$page->deleteObsoleteInstagramPostPages();

						return true;
					}
				],
				[
					'pattern' => '/remove-auth',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							return false;
						}

						$page->removeInstagramAuth();
						foreach ($page->children()->filterBy('intendedTemplate', 'instagram-post') as $page) {
							$page->kirby()->impersonate('kirby', fn() => $page->delete());
						}

						return true;
					}
				]
			];
		}
	],
	'socialstar-youtube-embed' => [
		'props' => [
			'label' => fn(string|null $label = null) => I18n::translate($label, $label) ?? I18n::translate('socialstar.youtube.video'),
			'value' => fn(string $value) => $value
		]
	],
	'socialstar-youtube-connector' => [
		'computed' => [
			'hasAuthCredentials' => fn() => SocialStar::option('youtube.apiKey') !== null,
			'userDetails' => function () {
				$value = Yaml::decode($this->value());
				if (!$value || !$value['id']) {
					return null;
				}

				$api = $this->model()->youtubeApi();
				try {
					$data = $api->cache('getAccountDetails', $value['id']);
					if (isset($data['items'])) {
						return $data['items'][0];
					}
				} catch (\Exception $e) {
				}

				return null;
			}
		],
		'api' => function () {
			return [
				[
					'pattern' => '/connect',
					'method' => 'POST',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							throw new Exception('Page not found');
						}
						$body = $page->kirby()->request()->body();
						$user = $page->youtubeApi()->getAccountIds(Str::trim(Str::trim($body->get('username'), '@')));

						if (!isset($user['items'])) {
							throw new Exception('User not found');
						}

						$page->save([
							$page::$youtubeFieldKey => [
								'playlist' => $user['items'][0]['contentDetails']['relatedPlaylists']['uploads'],
								'id' => $user['items'][0]['id'],
							]
						]);

						return true;
					}
				],
				[
					'pattern' => '/update',
					'method' => 'POST',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							return false;
						}

						$body = $page->kirby()->request()->body();
						$token = $body->get('pageToken');
						if (!$token) {
							$page->youtubeApi()->flushCache();
						}

						return ['token' => $page->fetchNewYouTubeVideos($token)];
					}
				],
				[
					'pattern' => '/cleanup',
					'method' => 'POST',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							return false;
						}

						$body = $page->kirby()->request()->body();
						$tokens = $body->get('pageTokens');

						$page->deleteObsoleteYouTubeVideoPages([null, ...$tokens]);

						return true;
					}
				],
				[
					'pattern' => '/disconnect',
					'method' => 'POST',
					'action' => function () {
						$page = $this->field()->model();
						if (!$page) {
							return false;
						}

						$page->removeYouTubeDetails();
						foreach ($page->children()->filterBy('intendedTemplate', 'youtube-video') as $page) {
							$page->kirby()->impersonate('kirby', fn() => $page->delete());
						}

						return true;
					}
				]
			];
		}
	]
];
