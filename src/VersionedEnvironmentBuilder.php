<?php

namespace Brera;

class VersionedEnvironmentBuilder implements EnvironmentBuilder
{
	/**
	 * @var DataVersion
	 */
	private $dataVersion;

	/**
	 * @var string
	 */
	private $themeDirectory;

	public function __construct(DataVersion $dataVersion, $themeDirectory)
	{
		$this->dataVersion = $dataVersion;
		$this->themeDirectory = $themeDirectory;
	}

	public function createEnvironmentFromXml($xmlString)
	{
		return new VersionedEnvironment($this->dataVersion, $this->themeDirectory);
	}
}
