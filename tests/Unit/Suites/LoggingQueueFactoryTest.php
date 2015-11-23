<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\Writer\StdOutLogMessageWriter;
use LizardsAndPumpkins\Queue\LoggingQueueDecorator;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\LoggingQueueFactory
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Queue\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\IntegrationTestFactory
 */
class LoggingQueueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggingQueueFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new LoggingQueueFactory();

        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        new IntegrationTestFactory($masterFactory);
        $masterFactory->register($this->factory);
    }

    public function testItReturnsADecoratedEventQueue()
    {
        $result = $this->factory->createEventQueue();
        $this->assertInstanceOf(LoggingQueueDecorator::class, $result);
        $this->assertAttributeInstanceOf(Queue::class, 'component', $result);
    }

    public function testItReturnsADecoratedCommandQueue()
    {
        $result = $this->factory->createCommandQueue();
        $this->assertInstanceOf(LoggingQueueDecorator::class, $result);
        $this->assertAttributeInstanceOf(Queue::class, 'component', $result);
    }

    public function testItReturnsAStdOutLogMessageWriter()
    {
        $result = $this->factory->createLogMessageWriter();
        $this->assertInstanceOf(StdOutLogMessageWriter::class, $result);
    }
}
