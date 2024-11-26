<?php

namespace tobimori\SocialStar\Instagram;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\Content;

class InstagramFeedPage extends Page
{
	use HasInstagramPosts;

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
				$details = $this->instagramApi()->getUserMedia($this->instagramAccessToken(), $id);

				$page = $this->kirby()->impersonate('kirby', fn() => Page::create([
					'slug' => $details['shortcode'],
					'template' => 'instagram-post',
					'draft' => false,
					'parent' => $this,
					'content' => [
						'title' => $details['caption'],
						'uuid' => $details['id'],
					]
				]));
			}

			$pages[] = $page;
		}

		return new Pages($pages, $this);
	}

	public function children(): Pages
	{
		return parent::children();
	}
}
