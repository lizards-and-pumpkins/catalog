<?php

namespace Brera\Http;

abstract class HttpRequest
{
	/**
	 * @var HttpUrl
	 */
	private $url;

	/**
	 * @param HttpUrl $url
	 */
	public function __construct(HttpUrl $url)
	{
		$this->url = $url;
	}

	/**
	 * @return HttpRequest
	 */
	public static function fromGlobalState()
	{
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		$protocol = 'http';
		if ($_SERVER['HTTPS']) {
			$protocol = 'https';
		}

		$url = HttpUrl::fromString($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		/* TODO: Decouple */
		return self::fromParameters($requestMethod, $url);
	}

	/**
	 * @param string $requestMethod
	 * @param HttpUrl $url
	 * @return HttpRequest
	 * @throws UnsupportedRequestMethodException
	 */
	public static function fromParameters($requestMethod, HttpUrl $url)
	{
		switch (strtoupper($requestMethod)) {
			case 'GET':
				return new HttpGetRequest($url);
			case 'POST':
				return new HttpPostRequest($url);
			default:
				throw new UnsupportedRequestMethodException(sprintf('Unsupported request method: "%s"', $requestMethod));
		}
	}

	/**
	 * @return HttpUrl
	 */
	public function getUrl()
	{
		return $this->url;
	}
} 
