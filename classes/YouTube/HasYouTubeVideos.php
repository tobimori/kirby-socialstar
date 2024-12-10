<?php

namespace tobimori\SocialStar\YouTube;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\Content;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use tobimori\SocialStar\SocialStar;

trait HasYouTubeVideos
{
	public static $youtubeFieldKey = 'youtubeConnector';

	public function youtubeField(): Content
	{
		return $this->content()->get(static::$youtubeFieldKey)->toObject();
	}

	public function youtubeApi(): Api
	{
		return Api::instance();
	}

	public function removeYouTubeDetails(): Page
	{
		return $this->save([
			static::$youtubeFieldKey => null
		]);
	}

	public function fetchNewYouTubeVideos(string|null $pageToken = null): string|null
	{
		$media = $this->youtubeApi()->cache('getPlaylistItems', $this->youtubeField()->playlist()->value(), $pageToken);

		foreach ($media['items'] as $video) {
			$id = $video['snippet']['resourceId']['videoId'];

			if (!($this->children()->find("page://{$id}"))) {
				$this->createYouTubeVideoPage($video['snippet']);
			}
		}

		if (isset($media['nextPageToken'])) {
			return $media['nextPageToken'];
		}

		return null;
	}

	/**
	 * Delete all youtube video pages that can't be found in the youtube feed api response
	 */
	public function deleteObsoleteYouTubeVideoPages(array $pageTokens): void
	{
		$ids = [];
		foreach ($pageTokens as $token) {
			$items = $this->youtubeApi()->cache('getPlaylistItems', $this->youtubeField()->playlist()->value(), $token);
			foreach ($items['items'] as $item) {
				$ids[] = $item['snippet']['resourceId']['videoId'];
			}
		}

		foreach ($this->children()->filterBy('intendedTemplate', 'youtube-video') as $page) {
			if (!in_array($page->uuid()->id(), $ids)) {
				$this->kirby()->impersonate('kirby', fn() => $page->delete());
			}
		}
	}

	public function createYouTubeVideoPage(array $data): Page
	{
		$id = $data['resourceId']['videoId'];
		$page = $this->kirby()->impersonate('kirby', fn() => Page::create([
			'slug' => $id,
			'template' => 'youtube-video',
			'draft' => false,
			'parent' => $this,
			'content' => [
				'title' => $data['title'],
				'description' => $data['description'],
				'uuid' => $id,
				'publishedAt' => date('Y-m-d H:i:s', strtotime($data['publishedAt'])),
			]
		]));

		$page = $this->kirby()->impersonate('kirby', fn() => $page->changeStatus('listed', strtotime($data['publishedAt'])));

		$thumbnail = array_key_last($data['thumbnails']);
		Dir::make($tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'socialstar_' . uniqid());
		$download = Remote::get($data['thumbnails'][$thumbnail]['url'], ['User-Agent' => SocialStar::userAgent()]);
		$filename = "{$id}.jpg";
		if ($download->code() === 200) {
			F::write($filepath = $tempDir . DIRECTORY_SEPARATOR . $filename, $download->content());
		}

		$this->kirby()->impersonate('kirby', fn() => $page->createFile([
			'source' => $filepath,
			'filename' => $filename,
			'template' => 'youtube-thumbnail',
		], move: true));
		Dir::remove($tempDir);

		return $page;
	}
}
