<?php

namespace tobimori\SocialStar\Instagram;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\Content;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use tobimori\SocialStar\SocialStar;

trait HasInstagramPosts
{
	public static $instagramFieldKey = 'instagramConnector';

	public function instagramField(): Content
	{
		return $this->content()->get(static::$instagramFieldKey)->toObject();
	}

	public function instagramApi(): Api
	{
		return Api::instance();
	}

	public function instagramAccessToken(): string
	{
		return $this->instagramField()->token()->value();
	}

	public function fetchNewInstagramPosts(): Pages
	{
		$media = $this->instagramApi()->getUserMediaIds($this->instagramAccessToken());

		$pages = [];
		foreach ($media['data'] as $post) {
			$id = $post['id'];

			if (!($page = $this->children()->find("page://{$id}"))) {
				$page = $this->createInstagramPostPage($id);
			}

			$pages[] = $page;
		}

		return new Pages($pages, $this);
	}

	public function deleteObsoleteInstagramPostPages(): void
	{
		$media = $this->instagramApi()->getUserMediaIds($this->instagramAccessToken());

		foreach ($this->children()->filterBy('template', 'instagram-post') as $page) {
			if (!in_array($page->uuid()->id(), $media['data'])) {
			}
		}
	}

	public function createInstagramPostPage(string $id): Page
	{
		$details = $this->instagramApi()->getUserMedia($this->instagramAccessToken(), $id);
		/** @var \tobimori\SocialStar\Instagram\InstagramPostPage $page */
		$page = $this->kirby()->impersonate('kirby', fn() => Page::create([
			'slug' => $details['shortcode'],
			'template' => 'instagram-post',
			'draft' => false,
			'num' => strtotime($details['timestamp']),
			'parent' => $this,
			'content' => [
				'caption' => $details['caption'] ?? "",
				'likeCount' => $details['like_count'],
				'commentsCount' => $details['comments_count'],
				'mediaType' => $details['media_type'],
				'permalink' => $details['permalink'],
				'uuid' => $details['id'],
				'timestamp' => $details['timestamp'],
			]
		]));

		$files = [$details['thumbnail_url'] ?? $details['media_url']];
		if ($details['media_type'] === 'CAROUSEL_ALBUM') {
			$children = $this->instagramApi()->getUserMediaChildren($this->instagramAccessToken(), $id);

			foreach ($children['data'] as $child) {
				$files[] = $child['media_url'];
			}
		}

		Dir::make($tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'socialstar_' . uniqid());
		foreach ($files as $file) {
			$download = Remote::get($file, ['User-Agent' => SocialStar::userAgent()]);
			$filename = Str::before(basename($file), '?');
			if ($download->code() === 200) {
				F::write($filepath = $tempDir . DIRECTORY_SEPARATOR . $filename, $download->content());
			}

			$this->kirby()->impersonate('kirby', fn() => $page->createFile([
				'source' => $filepath,
				'filename' => $filename,
				'template' => 'instagram-post-image',
			], move: true));
		}
		Dir::remove($tempDir);

		return $page;
	}

	public function refreshInstagramAccessToken(): void
	{
		// TODO: implement
	}
}
