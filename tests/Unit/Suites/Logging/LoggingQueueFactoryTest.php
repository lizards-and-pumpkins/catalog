<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\Messaging\Queue\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingQueueFactory
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 */
class LoggingQueueFactoryTest extends TestCase
{
    /**
     * @var LoggingQueueFactory
     */
    private $factory;

    protected function setUp()
    {
        $implementationFactory = new UnitTestFactory($this);
        
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register($implementationFactory);
        $this->factory = new LoggingQueueFactory($masterFactory);
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
