<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class InjectableDefaultWebFront extends DefaultWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;
    
    public function __construct(HttpRequest $request, MasterFactory $testMasterFactory, Factory $implementationFactory)
    {
        parent::__construct($request, $implementationFactory);
        $this->testMasterFactory = $testMasterFactory;
    }

    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return $this->testMasterFactory;
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        // The injected testing master factory already should have all factories set
    }
}
