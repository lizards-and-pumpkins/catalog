<?php

namespace Brera;

use Brera\Content\ContentBlockId;
use Brera\Content\ContentBlockSource;
use Brera\Content\UpdateContentBlockCommand;
use Brera\Product\ProjectProductStockQuantitySnippetCommandHandler;
use Brera\Product\ProjectProductStockQuantitySnippetCommand;

/**
 * @covers \Brera\CommandHandlerLocator
 * @uses   \Brera\Content\UpdateContentBlockCommand
 * @uses   \Brera\Product\ProjectProductStockQuantitySnippetCommand
 */
class CommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandHandlerLocator
     */
    private $locator;

    /**
     * @var CommonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMock(CommonFactory::class);
        $this->locator = new CommandHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->setExpectedException(UnableToFindCommandHandlerException::class);
        $this->locator->getHandlerFor($stubCommand);
    }

    public function testProjectProductQuantitySnippetCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(ProjectProductStockQuantitySnippetCommandHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProjectProductStockQuantitySnippetCommandHandler')
            ->willReturn($stubHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportCommand = new ProjectProductStockQuantitySnippetCommand('<xml/>');

        $result = $this->locator->getHandlerFor($productImportCommand);

        $this->assertInstanceOf(ProjectProductStockQuantitySnippetCommandHandler::class, $result);
    }

    public function testUpdateContentBlockCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(UpdateContentBlockCommand::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createUpdateContentBlockCommandHandler')
            ->willReturn($stubHandler);

        $stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $command = new UpdateContentBlockCommand($stubContentBlockId, $stubContentBlockSource);

        $result = $this->locator->getHandlerFor($command);

        $this->assertInstanceOf(UpdateContentBlockCommand::class, $result);
    }
}
