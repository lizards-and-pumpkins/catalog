<?php

namespace Brera;

interface MasterFactory
{
	/**
	 * @param Factory $factory
	 * @return null
	 */
	public function register(Factory $factory);
}
