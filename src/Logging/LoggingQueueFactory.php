<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\FactoryTrait;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\Messaging\Queue\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue\Queue;

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
