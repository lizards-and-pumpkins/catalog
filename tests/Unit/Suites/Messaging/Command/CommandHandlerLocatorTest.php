<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Import\ImportCatalogCommandHandler;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler;
use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 */
class CommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandHandlerLocator
     */
    private $locator;

    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $methods = array_merge(
            get_class_methods(CommandHandlerFactory::class),
            get_class_methods(MasterFactory::class)
        );
        $this->factory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods($methods)
            ->getMock();
        $this->locator = new CommandHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn('non_existing_foo');
        $this->expectException(UnableToFindCommandHandlerException::class);
        $this->locator->getHandlerFor($stubCommand);
    }

    public function testUpdateContentBlockCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->createMock(UpdateContentBlockCommandHandler::class);

        $this->factory->expects($this->once())
            ->method('createUpdateContentBlockCommandHandler')
            ->willReturn($stubHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn(UpdateContentBlockCommand::CODE);

        $result = $this->locator->getHandlerFor($stubCommand);

        $this->assertInstanceOf(UpdateContentBlockCommandHandler::class, $result);
    }

    public function testReturnsImportCatalogCommandHandler()
    {
        $stubHandler = $this->createMock(ImportCatalogCommandHandler::class);

        $this->factory->expects($this->once())
            ->method('createImportCatalogCommandHandler')
            ->willReturn($stubHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn(ImportCatalogCommand::CODE);

        $result = $this->locator->getHandlerFor($stubCommand);

        $this->assertInstanceOf(ImportCatalogCommandHandler::class, $result);
    }

    public function testReturnsUpdateTemplateCommandHandler()
    {
        $stubHandler = $this->createMock(UpdateTemplateCommandHandler::class);

        $this->factory->expects($this->once())
            ->method('createUpdateTemplateCommandHandler')
            ->willReturn($stubHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn(UpdateTemplateCommand::CODE);

        $result = $this->locator->getHandlerFor($stubCommand);

        $this->assertInstanceOf(UpdateTemplateCommandHandler::class, $result);
    }
}
