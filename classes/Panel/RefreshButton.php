<?php

namespace tobimori\SocialStar\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\Buttons\ViewButton;
use Kirby\Toolkit\I18n;

class RefreshButton extends ViewButton
{
	protected App $kirby;

	public function __construct(
		protected Page $model
	) {
		parent::__construct(
			icon: 'refresh',
			text: I18n::translate('socialstar.refresh'),
		);
	}
}
