<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Panel\Field;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use tobimori\SocialStar\Panel\RefreshButton;
use tobimori\SocialStar\Support\License;

return [
	'socialstar' => fn() => [
		'buttons' => [
			'refresh' => fn(Page $model) => new RefreshButton($model)
		],
		'dialogs' => [
			'socialstar/activate' => [
				'load' => fn() => [
					'component' => 'k-form-dialog',
					'props' => [
						'fields' => [
							'domain' => [
								'label' => t('socialstar.license.activate.label'),
								'type' => 'info',
								'theme' => ($isLocal = App::instance()->system()->isLocal()) ? 'warning' : 'info',
								'text' => tt(
									'socialstar.license.activate.' . ($isLocal ? 'local' : 'domain'),
									['domain' => App::instance()->system()->indexUrl()]
								),
							],
							'email' => Field::email(['required' => true]),
							'license' => [
								'label' => t('socialstar.license.key.label'),
								'type' => 'text',
								'required' => true,
								'counter' => false,
								'placeholder' => 'ST-XXX-1234XXXXXXXXXXXXXXXXXXXX',
								'help' => t('socialstar.license.key.help'),
							],
						],
						'submitButton' => [
							'icon' => 'key',
							'text' => t('socialstar.license.activate'),
							'theme' => 'love',
						]
					]
				],
				'submit' => function () {
					$body = App::instance()->request()->body();

					if (!V::email($body->get('email'))) {
						throw new Exception(t('socialstar.license.error.email'));
					}

					if (!Str::startsWith($body->get('license'), 'ST-STD-') && !Str::startsWith($body->get('license'), 'ST-ENT-')) {
						throw new Exception(t('socialstar.license.error.key'));
					}

					License::downloadLicense(
						email: $body->get('email'),
						license: $body->get('license')
					);

					return [
						'message' => 'License activated successfully!',
					];
				}
			],
		]
	]
];
