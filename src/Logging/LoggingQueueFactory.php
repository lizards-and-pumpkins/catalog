<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class LoggingQueueFactory implements Factory, MessageQueueFactory
{
    use FactoryTrait;

    /**
     * @var Queue
     */
    private $nonDecoratedEventMessageQueue;

    /**
     * @var Queue
     */
    private $nonDecoratedCommandMessageQueue;

    public function __construct(MasterFactory $masterFactory)
    {
        /** @var MessageQueueFactory $masterFactory */
        $this->nonDecoratedEventMessageQueue = $masterFactory->createEventMessageQueue();
        $this->nonDecoratedCommandMessageQueue = $masterFactory->createCommandMessageQueue();
    }

    public function createEventMessageQueue(): Queue
    {
        return new LoggingQueueDecorator(
            $this->nonDecoratedEventMessageQueue,
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createCommandMessageQueue(): Queue
    {
        return new LoggingQueueDecorator(
            $this->nonDecoratedCommandMessageQueue,
            $this->getMasterFactory()->getLogger()
        );
    }
}
