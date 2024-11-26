<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\A;
use tobimori\SocialStar\Instagram\Api;
use tobimori\SocialStar\SocialStar;

if (
	version_compare(App::version() ?? '0.0.0', '4.4.0', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '5.0.0', '>') === true
) {
	throw new Exception('Kirby DreamForm requires Kirby 4.4.0');
}

App::plugin(
	name: 'tobimori/socialstar',
	extends: [
		'options' => [
			'cache' => true, // TODO: maybe we need multiple caches, some for storing auth others for apis? to avoid accidental deletion?
			'ttl' => 60,
			'instagram' => [
				'appId' => null,
				'appSecret' => null,
				'callbackUri' => fn() => App::instance()->url() . "/socialstar/instagram/callback"
			]
		],
		'pageModels' => [
			'instagram-feed' => \tobimori\SocialStar\Instagram\InstagramFeedPage::class,
			'instagram-post' => \tobimori\SocialStar\Instagram\InstagramPostPage::class,
		],
		'fields' => [
			'socialstar-instagram-connector' => [
				'computed' => [
					'hasAuthCredentials' => function () {
						return SocialStar::option('instagram.appId') !== null && SocialStar::option('instagram.appSecret') !== null;
					},
					'authUrl' => function () {
						$this->model()->fetchNewInstagramPosts();
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
				]
			]
		],
		'routes' => [
			[
				'pattern' => '/socialstar/instagram/callback',
				'method' => 'GET',
				'lang' => '*',
				'action' => function () {
					// query params: code (= to request access token), state (= page uid & field key), error?
					$state = json_decode(get('state'), true);
					if (!$state) {
						return false;
					}

					$page = App::instance()->page($state['page']);

					if (!$page) {
						return false;
					}

					if (!get('error')) {
						try {
							$api = Api::instance();
							$shortLivedToken = $api->getAccessToken(get('code'))['access_token'];
							$longLivedToken = $api->getLongLivedAccessToken($shortLivedToken);

							$page->save([
								$state['field'] => [
									'token' => $longLivedToken['access_token'],
									'expires' => time() + $longLivedToken['expires_in'] - 1
								]
							]);
						} catch (\Exception $e) {
							// store error in session so we can show it to the user
						}
					}

					return Response::redirect($page->panel()->url());
				}
			]
		],
		'blueprints' => [
			'pages/instagram-feed' => __DIR__ . '/blueprints/pages/instagram-feed.yml',
			'pages/youtube-channel' => __DIR__ . '/blueprints/pages/youtube-channel.yml',
			'socialstar/instagram' => __DIR__ . '/blueprints/tabs/instagram.yml',
			'socialstar/youtube' => __DIR__ . '/blueprints/tabs/youtube.yml'
		],
		'translations' => A::keyBy(
			A::map(
				Dir::files(__DIR__ . '/translations'),
				function ($file) {
					$translations = [];
					foreach (Json::read(__DIR__ . '/translations/' . $file) as $key => $value) {
						$translations["socialstar.{$key}"] = $value;
					}

					return A::merge(
						['lang' => F::name($file)],
						$translations
					);
				}
			),
			'lang'
		)
	],
	info: [
		'homepage' => 'https://plugins.andkindness.com/socialstar'
	],
	version: '1.0.0'
);
