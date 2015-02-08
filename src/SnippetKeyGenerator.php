<?php

namespace Brera;

use Brera\Environment\Environment;

interface SnippetKeyGenerator
{
	/**
	 * @param mixed $identifier
	 * @param Environment $environment
	 * @return string
	 */
	public function getKeyForEnvironment($identifier, Environment $environment);
}
