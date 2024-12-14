<?php

namespace tobimori\SocialStar\Instagram;

use Kirby\Cms\Page;
use Kirby\Content\Field;

use Kirby\Toolkit\I18n;

class InstagramFeedPage extends Page
{
	use HasInstagramPosts;

	public function title(): Field
	{
		if ($this->instagramAccessToken()) {
			$userDetails = $this->instagramApi()->cache('getUserDetails', $this->instagramAccessToken());
			if (isset($userDetails['username'])) {
				return new Field($this, 'title',  I18n::template('socialstar.instagram.accountName', replace: ['name' => $userDetails['username']]));
			}
		}

		return new Field($this, 'title', I18n::translate('socialstar.instagram'));
	}
}
