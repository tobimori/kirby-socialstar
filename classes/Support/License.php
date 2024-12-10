<?php

namespace tobimori\SocialStar\Support;

use Kirby\Plugin\LicenseStatus;
use Kirby\Toolkit\Str;

class License extends \Kirby\Plugin\License
{
	public string $name;
	public string|null $link = null;
	public LicenseStatus $status;

	private function __construct(string $plugin)
	{
		$this->link = "https://plugins.andkindness.com/" . Str::slug($plugin);
		$this->name = $plugin . ' Standard-Lizenz';
		$this->status = LicenseStatus::from('demo');
	}

	public static function fromDisk(string $plugin): static
	{
		return new static($plugin);
	}

	public static function downloadLicense(string $email, string $license) {}
}
