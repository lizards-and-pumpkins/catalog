<?php

namespace Brera;

interface Factory
{
	/**
	 * @param MasterFactory $masterFactory
	 * @return mixed
	 */
	public function setMasterFactory(MasterFactory $masterFactory);
}
