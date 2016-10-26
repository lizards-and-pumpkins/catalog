<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class LoggingQueueFactory implements Factory, MessageQueueFactory
{
    use FactoryTrait;

    /**
     * @var MessageQueueFactory
     */
    private $implementationFactoryDelegate;

    public function __construct(MessageQueueFactory $implementationFactoryDelegate)
    {
        $this->implementationFactoryDelegate = $implementationFactoryDelegate;
    }

    public function createEventMessageQueue() : Queue
    {
        return new LoggingQueueDecorator(
            $this->implementationFactoryDelegate->createEventMessageQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createCommandMessageQueue() : Queue
    {
        return new LoggingQueueDecorator(
            $this->implementationFactoryDelegate->createCommandMessageQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }
}
