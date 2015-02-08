<?php

namespace Brera;

class DataVersion
{
	/**
	 * @var string
	 */
	private $version;

	/**
	 * @param string $version
	 * @return DataVersion
	 * @throws EmptyVersionException
	 * @throws InvalidVersionException
	 */
	public static function fromVersionString($version)
	{
		if (!is_string($version) && !is_int($version) && !is_float($version)) {
			throw new InvalidVersionException();
		}

		if (empty($version)) {
			throw new EmptyVersionException();
		}

		return new self((string) $version);
	}

	/**
	 * @param string $version
	 */
	private function __construct($version)
	{
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->version;
	}
}
