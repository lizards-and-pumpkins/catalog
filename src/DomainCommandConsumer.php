<?php

namespace Brera;

use Brera\Queue\Queue;

class DomainCommandConsumer
{
    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var DomainCommandHandlerLocator
     */
    private $commandHandlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Queue $commandQueue,
        DomainCommandHandlerLocator $commandHandlerLocator,
        Logger $logger
    ) {
        $this->commandQueue = $commandQueue;
        $this->commandHandlerLocator = $commandHandlerLocator;
        $this->logger = $logger;
    }

    public function process($numberOfMessagesToProcess)
    {
        for ($i=0; $i<$numberOfMessagesToProcess; $i++) {
            try {
                $domainCommand = $this->commandQueue->next();
                $this->processDomainCommand($domainCommand);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainCommandQueueMessage($e));
            }
        }
    }

    private function processDomainCommand(DomainCommand $domainCommand)
    {
        try {
            $domainEventHandler = $this->commandHandlerLocator->getHandlerFor($domainCommand);
            $domainEventHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new DomainCommandHandlerFailedMessage($domainCommand, $e));
        }
    }
}
