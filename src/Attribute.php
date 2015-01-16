<?php

namespace Brera;

interface Attribute
{
	/**
	 * @param string $codeExpectation
	 * @return bool
	 */
	public function isCodeEqualsTo($codeExpectation);

	/**
	 * @return string
	 */
	public function getCode();

	/**
	 * @return string
	 */
	public function getValue();
}
