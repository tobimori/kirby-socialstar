<?php

namespace tobimori\SocialStar\Instagram;

use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Content\VersionId;
use Kirby\Toolkit\I18n;

class InstagramPostPage extends Page
{
	public function info(): string
	{
		return I18n::template('socialstar.instagram.post.info', replace: [
			'likes' => $this->likeCount()->toInt(),
			'comments' => $this->commentsCount()->toInt(),
		]);
	}

	public function title(): Field
	{
		if ($this->mediaType()->value() === 'VIDEO') {
			return new Field($this, 'title', I18n::translate('socialstar.instagram.reel'));
		}

		return $this->content()->get('caption')->excerpt(64);
	}

	public function render(array $data = [], $contentType = 'html', VersionId|string|null $versionId = null): string
	{
		if ($this->intendedTemplate()->name() !== $this->template()->name()) {
			go($this->content()->get('permalink')->value());
		}

		return parent::render($data, $contentType . $versionId);
	}

	public function updatePostPage(): static
	{
		$newDetails = $this->parent()->instagramApi()->getUserMedia($this->parent()->instagramAccessToken(), $this->uuid()->id());

		// we don't fetch for new files but they're immutable anyway so it's fine
		return $this->kirby()->impersonate('kirby', fn() => $this->update([
			'caption' => $newDetails['caption'] ?? "",
			'likeCount' => $newDetails['like_count'],
			'commentsCount' => $newDetails['comments_count'],
			'mediaType' => $newDetails['media_type'],
			'permalink' => $newDetails['permalink'],
			'timestamp' => $newDetails['timestamp'],
		]));
	}
}
