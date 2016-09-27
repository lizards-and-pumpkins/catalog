<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingQueueFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
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
        $implementationFactory = new UnitTestFactory($this);
        
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register($implementationFactory);
        $this->factory = new LoggingQueueFactory($implementationFactory);
        $masterFactory->register($this->factory);
    }

    public function testImplementsMessageQueueFactory()
    {
        $this->assertInstanceOf(MessageQueueFactory::class, $this->factory);
    }

    public function testItReturnsADecoratedEventQueue()
    {
        $result = $this->factory->createEventMessageQueue();
        $this->assertInstanceOf(LoggingQueueDecorator::class, $result);
        $this->assertAttributeInstanceOf(Queue::class, 'decoratedQueue', $result);
    }

    public function testItReturnsADecoratedCommandQueue()
    {
        $result = $this->factory->createCommandMessageQueue();
        $this->assertInstanceOf(LoggingQueueDecorator::class, $result);
        $this->assertAttributeInstanceOf(Queue::class, 'decoratedQueue', $result);
    }
}
