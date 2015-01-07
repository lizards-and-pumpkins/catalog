<?php

namespace Brera;

interface SnippetRendererCollection
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param Environment $environment
	 * @return SnippetResultList
	 * @throws InvalidProjectionDataSourceType
	 */
	public function render(ProjectionSourceData $dataObject, Environment $environment);
}
