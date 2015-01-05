<?php

namespace Brera;

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
}
