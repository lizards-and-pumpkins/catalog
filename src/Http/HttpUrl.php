<?php

namespace Brera\Http;

use League\Url\UrlImmutable;
use League\Url\AbstractUrl;

class HttpUrl
{
	/**
	 * @var \League\Url\AbstractUrl
	 */
	private $url;

	/**
	 * @param \League\Url\AbstractUrl $url
	 */
	protected function __construct(AbstractUrl $url)
	{
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->url;
	}

	/**
	 * @param string $urlString
	 * @return HttpUrl
	 * @throws UnknownProtocolException
	 */
	public static function fromString($urlString)
	{
		try {
			$url = UrlImmutable::createFromUrl($urlString);
		} catch (\RuntimeException $e) {
			throw new \InvalidArgumentException($e->getMessage());
		}

		return self::createHttpUrlBasedOnSchema($url);
	}

	/**
	 * @return bool
	 */
	public function isProtocolEncrypted()
	{
		return false;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		/** @var \League\Url\Components\Path $path */
		$path = $this->url->getPath();

		return $path->getUriComponent();
	}

	/**
	 * @param \League\Url\AbstractUrl $url
	 * @return HttpUrl
	 * @throws UnknownProtocolException
	 */
	private static function createHttpUrlBasedOnSchema(AbstractUrl $url)
	{
		switch ($url->getScheme()) {
			case 'https':
				return new HttpsUrl($url);
			case 'http':
				return new HttpUrl($url);
			default:
				throw new UnknownProtocolException(sprintf('Protocol can not be handled "%s"', $url->getScheme()));
		}
	}
} 
