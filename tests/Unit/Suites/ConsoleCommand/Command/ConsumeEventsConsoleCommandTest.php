<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ConsumeEventsConsoleCommand
 */
class ConsumeEventsConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    /**
     * @var AnyInvokedCount
     */
    private $registerFactorySpy;

    private function getRegisteredFactoryClassNames()
    {
        return array_map(function (ObjectInvocation $invocation) {
            return get_class($invocation->getParameters()[0]);
        }, $this->registerFactorySpy->getInvocations());
    }

    protected function setUp()
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createDomainEventConsumer']))
            ->disableOriginalConstructor()
            ->getMock();
        $this->registerFactorySpy = new AnyInvokedCount();
        $this->stubMasterFactory->expects($this->registerFactorySpy)->method('register');
    }

    public function testIsAConsoleCommand()
    {
        $command = new ConsumeEventsConsoleCommand($this->stubMasterFactory);
        $this->assertInstanceOf(ConsoleCommand::class, $command);
    }

    public function testRegistersUpdatingCommandFactories()
    {
        $registrationSpy = $this->any();
        $this->stubMasterFactory->expects($registrationSpy)->method('register');
        new ConsumeEventsConsoleCommand($this->stubMasterFactory);

        $registeredFactoryClassNames = $this->getRegisteredFactoryClassNames();
        $this->assertContains(UpdatingProductImportCommandFactory::class, $registeredFactoryClassNames);
        $this->assertContains(UpdatingProductImageImportCommandFactory::class, $registeredFactoryClassNames);
        $this->assertContains(UpdatingProductListingImportCommandFactory::class, $registeredFactoryClassNames);
    }

    public function testCallsProcessOnDomainEventConsumer()
    {
        $mockDomainEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockDomainEventConsumer->expects($this->once())->method('process');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockDomainEventConsumer);
        
        (new ConsumeEventsConsoleCommand($this->stubMasterFactory))->run();
    }
}
