<?php
namespace Brera;

use Brera\Environment\EnvironmentSource;

interface SnippetRenderer
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param EnvironmentSource $environment
	 * @return SnippetResultList
	 */
	public function render(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
