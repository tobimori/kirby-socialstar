<?php

use Kirby\Cms\App;

return [
	'cache' => true, // TODO: maybe we need multiple caches, some for storing auth others for apis? to avoid accidental deletion?
	'ttl' => 60,
	'instagram' => [
		'appId' => null,
		'appSecret' => null,
		'callbackUri' => fn() => App::instance()->url() . "/socialstar/instagram/callback"
	]
];
