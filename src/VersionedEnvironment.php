<?php
namespace Brera;

class VersionedEnvironment implements Environment
{
	/**
	 * @var DataVersion
	 */
	private $version;

	/**
	 * @var string
	 */
	private $themeDirectory;

	/**
	 * @param DataVersion $version
	 * @param $themeDirectory
	 */
	public function __construct(DataVersion $version, $themeDirectory)
	{
		$this->version = $version;
		$this->themeDirectory = $themeDirectory;
	}

	/**
	 * @return DataVersion
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getThemeDirectory()
	{
		return $this->themeDirectory;
	}
}
