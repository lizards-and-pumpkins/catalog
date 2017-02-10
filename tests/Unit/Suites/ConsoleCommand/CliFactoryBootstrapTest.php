<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\CliFactoryBootstrap
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 */
class CliFactoryBootstrapTest extends TestCase
{
    private function createSpyFactory(): Factory
    {
        return new class implements Factory, FactoryWithCallback {
            use FactoryTrait;

            public $wasRegistered = false;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->wasRegistered = true;
            }
        };
    }

    private function createSpyCommonFactory()
    {
        $spyCommonFactory = new class implements Factory, FactoryWithCallback
        {
            use FactoryTrait;

            private static $registrationCount = 0;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                static::$registrationCount++;
            }

            public function getRegistrationCount(): int
            {
                return static::$registrationCount;
            }

            public function resetRegistrationCount()
            {
                static::$registrationCount = 0;
            }
        };
        $spyCommonFactory->resetRegistrationCount();
        
        return $spyCommonFactory;
    }

    public function testReturnsMasterFactoryInstance()
    {
        $this->assertInstanceOf(MasterFactory::class, (CliFactoryBootstrap::createFactory()));
    }

    public function testRegistersAnySpecifiedFactories()
    {
        $spyFactoryA = $this->createSpyFactory();
        $spyFactoryB = $this->createSpyFactory();
        CliFactoryBootstrap::createFactory($spyFactoryA, $spyFactoryB);
        $this->assertTrue($spyFactoryA->wasRegistered);
        $this->assertTrue($spyFactoryB->wasRegistered);
    }

    public function testRegistersCommonFactoryWithoutItBeingSpecified()
    {
        /** @var CliFactoryBootstrap $testCliBootstrap */
        $testCliBootstrap = new class extends CliFactoryBootstrap {
            public function setCommonFactoryClass(string $commonFactoryClass)
            {
                static::$commonFactoryClass = $commonFactoryClass;
            }
        };
        
        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createFactory();

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testDoesNotRegisterDefaultFactoryIfAlsoSpecifiedAsArgument()
    {
        /** @var CliFactoryBootstrap $testCliBootstrap */
        $testCliBootstrap = new class extends CliFactoryBootstrap {
            public function setCommonFactoryClass(string $commonFactoryClass)
            {
                static::$commonFactoryClass = $commonFactoryClass;
            }
        };

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));
        
        $testCliBootstrap->createFactory($spyCommonFactory);
        
        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }
}

