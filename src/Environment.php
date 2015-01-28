<?php
namespace Brera;

interface Environment
{
	/**
	 * @return DataVersion
	 */
	public function getVersion();

	/**
	 * @return string
	 */
	public function getThemeDirectory();
}
