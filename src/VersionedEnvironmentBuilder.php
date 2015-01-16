<?php

namespace Brera;

class VersionedEnvironmentBuilder implements EnvironmentBuilder
{
	/**
	 * @var DataVersion
	 */
	private $dataVersion;

	public function __construct(DataVersion $dataVersion)
	{
		$this->dataVersion = $dataVersion;
	}

	public function createEnvironmentFromXml($xmlString)
	{
		return new VersionedEnvironment($this->dataVersion);
	}
}
