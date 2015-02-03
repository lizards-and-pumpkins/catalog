<?php

namespace Brera;

interface Projector
{
	/**
	 * @param ProjectionSourceData $dataObject
	 * @param EnvironmentSource $environment
	 * @throws InvalidProjectionDataSourceType
	 * @return null
	 */
	public function project(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
