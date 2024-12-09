<?php

namespace tobimori\SocialStar\YouTube;

use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Toolkit\I18n;

class YouTubeChannelPage extends Page
{
	use HasYouTubeVideos;

	public function title(): Field
	{
		if ($this->youtubeField()->id()->isNotEmpty()) {
			$userDetails = $this->youtubeApi()->cache('getAccountDetails', $this->youtubeField()->id()->value());
			if (isset($userDetails['items'])) {
				return new Field($this, 'title',  I18n::template('socialstar.youtube.accountName', replace: ['name' => $userDetails['items'][0]['snippet']['customUrl']]));
			}
		}

		return new Field($this, 'title', I18n::translate('socialstar.youtube'));
	}
}
