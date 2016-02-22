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
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 */
class LoggingQueueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggingQueueFactory
     */
    private $factory;

    protected function setUp()
    {

        $implementationFactory = new UnitTestFactory();
        
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register($implementationFactory);
        $this->factory = new LoggingQueueFactory($implementationFactory);
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
}
