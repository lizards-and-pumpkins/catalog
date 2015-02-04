<?php

namespace Brera;

interface SnippetRendererCollection
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param EnvironmentSource $environment
	 * @return SnippetResultList
	 * @throws InvalidProjectionDataSourceType
	 */
	public function render(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
