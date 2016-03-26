<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class StubFactory implements Factory
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function setMasterFactory(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }

    /**
     * @param string $parameter
     * @return string
     */
    public function createSomething($parameter)
    {
        return $parameter;
    }

    public function getSomething()
    {

    }

    public function doSomething()
    {

    }

    protected function createSomethingProtected()
    {
        $this->getSomethingPrivate();
    }

    private function getSomethingPrivate()
    {

    }
}
