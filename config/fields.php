<?php

use Kirby\Data\Yaml;
use tobimori\SocialStar\Instagram\Api;
use tobimori\SocialStar\SocialStar;

return [
	'socialstar-instagram-connector' => [
		'computed' => [
			'hasAuthCredentials' => function () {
				return SocialStar::option('instagram.appId') !== null && SocialStar::option('instagram.appSecret') !== null;
			},
			'authUrl' => function () {
				return Api::instance()->getOAuthUrl(['state' => json_encode([
					'page' => $this->model()->uid(),
					'field' => $this->name()
				])]);
			},
			'userDetails' => function () {
				$value = Yaml::decode($this->value());

				if (!$value || !$value['token']) {
					return null;
				}

				$api = Api::instance();
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
		'api' => [
			[
				'pattern' => '/load-new-posts',
				'action' => function () {
					$this->model()->fetchNewInstagramPosts();
					$this->model()->deleteObsoleteInstagramPostPages();

					return true;
				}
			],
			[
				'pattern' => '/remove-auth',
				'action' => function () {
					$this->model()->fetchNewInstagramPosts();
					$this->model()->deleteObsoleteInstagramPostPages();

					return true;
				}
			]
		]
	],
];
