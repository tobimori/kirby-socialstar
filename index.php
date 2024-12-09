<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;

if (
	version_compare(App::version() ?? '0.0.0', '5.0.0-beta.1', '<') === true ||
	version_compare(App::version() ?? '0.0.0', '6.0.0', '>') === true
) {
	throw new Exception('Kirby SocialStar requires Kirby 5');
}

App::plugin(
	name: 'tobimori/socialstar',
	extends: [
		'options' => require __DIR__ . '/config/options.php',
		'pageModels' => [
			'instagram-feed' => \tobimori\SocialStar\Instagram\InstagramFeedPage::class,
			'instagram-post' => \tobimori\SocialStar\Instagram\InstagramPostPage::class,
		],
		'sections' => require __DIR__ . '/config/sections.php',
		'fields' => require __DIR__ . '/config/fields.php',
		'routes' => require __DIR__ . '/config/routes.php',
		'blueprints' => [
			'files/instagram-post-image' => __DIR__ . '/blueprints/files/instagram-post-image.yml',
			'pages/instagram-feed' => __DIR__ . '/blueprints/pages/instagram-feed.yml',
			'pages/instagram-post' => __DIR__ . '/blueprints/pages/instagram-post.yml',
			'pages/youtube-channel' => __DIR__ . '/blueprints/pages/youtube-channel.yml',
			'socialstar/instagram' => __DIR__ . '/blueprints/tabs/instagram.yml',
			'socialstar/instagram-post' => __DIR__ . '/blueprints/tabs/instagram-post.yml',
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
