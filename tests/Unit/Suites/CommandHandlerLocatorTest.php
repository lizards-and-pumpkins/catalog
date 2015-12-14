<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\ContentBlockSource;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Exception\UnableToFindCommandHandlerException;

/**
 * @covers \LizardsAndPumpkins\CommandHandlerLocator
 * @uses   \LizardsAndPumpkins\Content\UpdateContentBlockCommand
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
        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Command::class);
        $this->setExpectedException(UnableToFindCommandHandlerException::class);
        $this->locator->getHandlerFor($stubCommand);
    }

    public function testUpdateContentBlockCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(UpdateContentBlockCommandHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createUpdateContentBlockCommandHandler')
            ->willReturn($stubHandler);

        /** @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject $stubContentBlockSource */
        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $command = new UpdateContentBlockCommand($stubContentBlockSource);

        $result = $this->locator->getHandlerFor($command);

        $this->assertInstanceOf(UpdateContentBlockCommandHandler::class, $result);
    }
}
