<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

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

    public function createSomething(string $parameter) : string
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
