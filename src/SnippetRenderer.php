<?php
namespace Brera;

use Brera\Environment\EnvironmentSource;

interface SnippetRenderer
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param EnvironmentSource $environmentSource
	 * @return SnippetResultList
	 */
	public function render(ProjectionSourceData $dataObject, EnvironmentSource $environmentSource);
}
