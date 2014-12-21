<?php

namespace Brera\PoC;

interface Attribute
{
	/**
	 * @return string
	 */
	public function getCode();

	/**
	 * @return string
	 */
	public function getValue();

	/**
	 * @return array
	 */
	public function getEnvironment();
}
