<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\CommandHandler;

/**
 * @covers \Brera\Product\ProjectProductStockQuantitySnippetCommandHandler
 */
class ProjectProductStockQuantitySnippetCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectProductStockQuantitySnippetCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var ProductStockQuantityProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductStockQuantitySourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStockQuantitySourceBuilder;

    /**
     * @var ProjectProductStockQuantitySnippetCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockCommand = $this->getMock(ProjectProductStockQuantitySnippetCommand::class, [], [], '', false);
        $this->mockProjector = $this->getMock(ProductStockQuantityProjector::class, [], [], '', false);
        $this->mockProductStockQuantitySourceBuilder = $this->getMock(
            ProductStockQuantitySourceBuilder::class,
            [],
            [],
            '',
            false
        );
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->commandHandler = new ProjectProductStockQuantitySnippetCommandHandler(
            $this->mockCommand,
            $this->mockProductStockQuantitySourceBuilder,
            $stubContextSource,
            $this->mockProjector
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }
    
    public function testProductQuantitySnippetProjectionIsTriggered()
    {
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->mockProductStockQuantitySourceBuilder->expects($this->any())
            ->method('createFromXml')
            ->willReturn($stubProductStockQuantitySource);

        $this->mockProjector->expects($this->once())
            ->method('project');

        $this->commandHandler->process();
    }
}
