<?php

return [
	'socialstar-instagram-post-actions' => [
		'api' => function () {
			return [
				[
					'pattern' => '/refresh-data',
					'action' => function () {
						$page = $this->section()->model();
						if (!$page) {
							return false;
						}

						$page->updatePostPage();

						return true;
					}
				]
			];
		}
	]
];
