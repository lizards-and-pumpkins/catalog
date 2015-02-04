<?php
namespace Brera;

interface SnippetRenderer
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param EnvironmentSource $environment
	 * @return SnippetResultList
	 */
	public function render(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
