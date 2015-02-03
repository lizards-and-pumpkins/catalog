<?php
namespace Brera;

class VersionedEnvironment implements Environment
{
	const CODE = 'version';
	
	/**
	 * @var DataVersion
	 */
	private $version;

	/**
	 * @param array $environmentSource
	 */
	public function __construct(array $environmentSource)
	{
		$this->version = $environmentSource[self::CODE];
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public function getValue($code)
	{
		return (string) $this->version;
	}

	public function getCode()
	{
		return self::CODE;
	}
}
