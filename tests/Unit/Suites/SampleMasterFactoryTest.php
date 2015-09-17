<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\SampleMasterFactory
 * @covers \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\StubFactory
 */
class SampleMasterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleMasterFactory
     */
    private $sampleMasterFactory;

    /**
     * @var StubFactory
     */
    private $stubFactory;

    protected function setUp()
    {
        $this->sampleMasterFactory = new SampleMasterFactory;
        $this->stubFactory = new StubFactory;
        $this->sampleMasterFactory->register($this->stubFactory);
    }

    public function testMasterFactoryIsSetOnChildFactory()
    {
        $this->assertAttributeEquals($this->sampleMasterFactory, 'masterFactory', $this->stubFactory);
    }

    public function testOnlyPublicFactoryMethodsStartingWithGetOrCreateAreRegisteredOnMasterFactory()
    {
        $expectedMethods = ['createSomething' => $this->stubFactory, 'getSomething' => $this->stubFactory];
        $this->assertAttributeSame($expectedMethods, 'methods', $this->sampleMasterFactory);
    }

    public function testExceptionIsThrownDuringAttemptToCallNotRegisteredFactoryMethod()
    {
        $this->setExpectedException(UndefinedFactoryMethodException::class);
        $this->sampleMasterFactory->nonRegisteredMethod();
    }

    public function testRegisteredFactoryMethodsCanBeCalled()
    {
        $parameter = 'foo';
        $result = $this->sampleMasterFactory->createSomething($parameter);

        $this->assertSame($parameter, $result);
    }
}
