<?php

namespace tobimori\SocialStar;

use Kirby\Cms\App;
use tobimori\SocialStar\Support\License;

final class SocialStar
{
	/**
	 * Returns the user agent string for the plugin
	 */
	public static function userAgent(): string
	{
		return "Kirby SocialStar/" . App::plugin('tobimori/socialstar')->version() . " (+https://plugins.andkindness.com/socialstar)";
	}

	/**
	 * Returns a plugin option
	 */
	public static function option(string $key, mixed $default = null): mixed
	{
		$option = App::instance()->option("tobimori.socialstar.{$key}", $default);
		if (is_callable($option)) {
			$option = $option();
		}

		return $option;
	}

	public static function license(): License
	{
		return License::fromDisk('SocialStar');
	}
}
