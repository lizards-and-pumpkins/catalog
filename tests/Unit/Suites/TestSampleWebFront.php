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
}
