<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\UnitTestFactory;
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

    private function createTestCliFactoryBootstrap(): CliFactoryBootstrap
    {
        return new class extends CliFactoryBootstrap
        {
            private static $originalCommonFactory;
            
            public function setCommonFactoryClass(string $commonFactoryClass)
            {
                if (is_null(static::$originalCommonFactory)) {
                    static::$originalCommonFactory = static::$commonFactoryClass;
                }
                static::$commonFactoryClass = $commonFactoryClass;
            }
            
            public function __destruct()
            {
                if (! is_null(static::$originalCommonFactory)) {
                    static::$commonFactoryClass = static::$originalCommonFactory;
                }
            }
        };
    }
    
    public function testReturnsMasterFactoryInstance()
    {
        $this->assertInstanceOf(MasterFactory::class, (CliFactoryBootstrap::createMasterFactory()));
    }

    public function testRegistersAnySpecifiedFactories()
    {
        $spyFactoryA = $this->createSpyFactory();
        $spyFactoryB = $this->createSpyFactory();
        CliFactoryBootstrap::createMasterFactory($spyFactoryA, $spyFactoryB);
        $this->assertTrue($spyFactoryA->wasRegistered);
        $this->assertTrue($spyFactoryB->wasRegistered);
    }

    public function testRegistersCommonFactoryWithoutItBeingSpecified()
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();
        
        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createMasterFactory();

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testDoesNotRegisterDefaultFactoryIfAlsoSpecifiedAsArgument()
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));
        
        $testCliBootstrap->createMasterFactory($spyCommonFactory);
        
        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testReturnsAMasterFactoryWhenALoggingFactoryIsRequested()
    {
        $factory = CliFactoryBootstrap::createLoggingMasterFactory(new UnitTestFactory($this));
        $this->assertInstanceOf(MasterFactory::class, $factory);
    }

    public function testLoggingFactoriesAreRegistered()
    {
        $factory = CliFactoryBootstrap::createLoggingMasterFactory(new UnitTestFactory($this));
        
        $queue = $factory->createEventMessageQueue();
        $commandHandler = $factory->createUpdateContentBlockCommandHandler();
        $eventHandler = $factory->createTemplateWasUpdatedDomainEventHandler();
        
        $this->assertInstanceOf(LoggingQueueDecorator::class, $queue);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $eventHandler);
    }
}

