<?php


namespace Brera;

use Brera\Http\HttpRequest;

class TestSampleWebFront extends SampleWebFront
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testMasterFactory;

    /**
     * @var FrontendFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testFrontendFactory;
    
    public function __construct(
        HttpRequest $request,
        MasterFactory $testMasterFactory,
        FrontendFactory $frontendFactory
    ) {
        parent::__construct($request);
        $this->testMasterFactory = $testMasterFactory;
        $this->testFrontendFactory = $frontendFactory;
    }

    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return $this->testMasterFactory;
    }

    protected function registerSharedFactories(MasterFactory $masterFactory)
    {
        // Shared factories already should be registered on the injected factory
    }

    protected function registerFrontendFactory(MasterFactory $masterFactory)
    {
        $this->setFrontendFactoryForTestability($this->testFrontendFactory);
    }


}
