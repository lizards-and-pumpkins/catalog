<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator;
use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
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
     * @var CommonFactory|\PHPUnit_Framework_MockObject_MockObject
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
        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Command::class);
        $this->expectException(UnableToFindCommandHandlerException::class);
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
