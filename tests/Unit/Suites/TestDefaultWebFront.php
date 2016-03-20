<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;

class TestDefaultWebFront extends DefaultWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;

    public function __construct(HttpRequest $request, MasterFactory $testMasterFactory)
    {
        parent::__construct($request, new UnitTestFactory());
        $this->testMasterFactory = $testMasterFactory;
    }

    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return $this->testMasterFactory;
    }
}
