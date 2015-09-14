<?php


namespace Brera;

use Brera\Log\Writer\StdOutLogMessageWriter;
use Brera\Queue\LoggingQueueDecorator;
use Brera\Queue\Queue;

/**
 * @covers \Brera\LoggingQueueFactory
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\Queue\LoggingQueueDecorator
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\IntegrationTestFactory
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
        $masterFactory->register(new IntegrationTestFactory());
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
