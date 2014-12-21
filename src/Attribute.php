<?php

namespace Brera\PoC;

interface Attribute
{
	/**
	 * @param string $codeExpectation
	 * @return bool
	 */
	public function hasCode($codeExpectation);

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
