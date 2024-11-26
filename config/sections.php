<?php

return [
	'socialstar-instagram-post-actions' => [
		'api' => function () {
			return [
				[
					'pattern' => '/say-hello',
					'action' => function () {
						return [
							'message' => 'Hello, World!'
						];
					}
				]
			];
		}
	]
];
