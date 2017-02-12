<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\CatalogMasterFactory
 * @covers \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\StubFactory
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
}
