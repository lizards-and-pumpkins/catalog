<?php

namespace Brera;

use Brera\Product\ProjectProductStockQuantitySnippetDomainCommandHandler;
use Brera\Product\ProjectProductStockQuantitySnippetDomainCommand;

/**
 * @covers \Brera\DomainCommandHandlerLocator
 * @uses   \Brera\Product\ProjectProductStockQuantitySnippetDomainCommand
 */
class DomainCommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainCommandHandlerLocator
     */
    private $locator;

    /**
     * @var CommonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMock(CommonFactory::class);
        $this->locator = new DomainCommandHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        $stubDomainCommand = $this->getMock(DomainCommand::class);
        $this->setExpectedException(UnableToFindDomainCommandHandlerException::class);
        $this->locator->getHandlerFor($stubDomainCommand);
    }

    public function testProjectProductQuantitySnippetDomainCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(ProjectProductStockQuantitySnippetDomainCommandHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProjectProductStockQuantitySnippetDomainCommandHandler')
            ->willReturn($stubHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportDomainCommand = new ProjectProductStockQuantitySnippetDomainCommand('<xml/>');

        $result = $this->locator->getHandlerFor($productImportDomainCommand);

        $this->assertInstanceOf(ProjectProductStockQuantitySnippetDomainCommandHandler::class, $result);
    }
}
