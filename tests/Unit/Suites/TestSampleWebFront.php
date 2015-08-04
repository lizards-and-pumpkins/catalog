<?php


namespace Brera;

use Brera\Http\HttpRequest;

class TestSampleWebFront extends SampleWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;
    
    public function __construct(HttpRequest $request, MasterFactory $testMasterFactory)
    {
        parent::__construct($request);
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
        // The injected test master factory should already contains all required factories
    }


}
