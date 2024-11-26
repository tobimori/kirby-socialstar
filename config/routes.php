<?php

use Kirby\Cms\App;
use Kirby\Cms\Response;
use tobimori\SocialStar\Instagram\Api;

return [
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

					$page->fetchNewInstagramPosts();
				} catch (\Exception $e) {
					// store error in session so we can show it to the user
				}
			}

			return Response::redirect($page->panel()->url());
		}
	]
];
