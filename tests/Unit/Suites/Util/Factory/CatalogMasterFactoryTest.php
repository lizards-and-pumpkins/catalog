<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\CatalogMasterFactory
 * @covers \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\StubFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 */
class CatalogMasterFactoryTest extends TestCase
{
    /**
     * @var CatalogMasterFactory
     */
    private $catalogMasterFactory;

    /**
     * @var StubFactory
     */
    private $stubFactory;

    protected function setUp()
    {
        $this->catalogMasterFactory = new CatalogMasterFactory;
        $this->stubFactory = new StubFactory;
        $this->catalogMasterFactory->register($this->stubFactory);
    }

    public function testMasterFactoryIsSetOnChildFactory()
    {
        $this->assertAttributeEquals($this->catalogMasterFactory, 'masterFactory', $this->stubFactory);
    }

    public function testOnlyPublicFactoryMethodsStartingWithGetOrCreateAreRegisteredOnMasterFactory()
    {
        $expectedMethods = ['createSomething' => $this->stubFactory, 'getSomething' => $this->stubFactory];
        $this->assertAttributeSame($expectedMethods, 'methods', $this->catalogMasterFactory);
    }

    public function testExceptionIsThrownDuringAttemptToCallNotRegisteredFactoryMethod()
    {
        $this->expectException(UndefinedFactoryMethodException::class);
        $this->catalogMasterFactory->nonRegisteredMethod();
    }

    public function testRegisteredFactoryMethodsCanBeCalled()
    {
        $parameter = 'foo';
        $result = $this->catalogMasterFactory->createSomething($parameter);

        $this->assertSame($parameter, $result);
    }

    public function testHasReturnsIfMethodIsKnownOrNot()
    {
        $dummyFactory = new class implements Factory
        {
            use FactoryTrait;

            public function createFoo() { }
        };
        $this->catalogMasterFactory->register($dummyFactory);
        $this->assertTrue($this->catalogMasterFactory->hasMethod('createFoo'));
        $this->assertFalse($this->catalogMasterFactory->hasMethod('createBar'));
    }

    public function testCallsFactoryCallbackMethods()
    {
        $factoryWithCallbacks = new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;
            
            public $beforeFactoryRegistrationCallbackWasCalled = false;
            public $factoryRegistrationCallbackWasCalled = false;

            public function beforeFactoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->beforeFactoryRegistrationCallbackWasCalled = true;
            }

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->factoryRegistrationCallbackWasCalled = true;
            }
        };
        $this->catalogMasterFactory->register($factoryWithCallbacks);
        $this->assertTrue($factoryWithCallbacks->beforeFactoryRegistrationCallbackWasCalled);
        $this->assertTrue($factoryWithCallbacks->factoryRegistrationCallbackWasCalled);
    }
}
