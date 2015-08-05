<?php

namespace Brera;

use Brera\Queue\Queue;

class CommandConsumer
{
    private $maxNumberOfMessagesToProcess = 200;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var CommandHandlerLocator
     */
    private $commandHandlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $commandQueue, CommandHandlerLocator $commandHandlerLocator, Logger $logger)
    {
        $this->commandQueue = $commandQueue;
        $this->commandHandlerLocator = $commandHandlerLocator;
        $this->logger = $logger;
    }

    public function process()
    {
        $numberOfMessagesBeforeReturn = $this->maxNumberOfMessagesToProcess;

        while ($this->commandQueue->count() > 0 && $numberOfMessagesBeforeReturn-- > 0) {
            try {
                $domainEvent = $this->commandQueue->next();
                $this->processCommand($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromCommandQueueMessage($e));
            }
        }
    }

    private function processCommand(Command $command)
    {
        try {
            $commandHandler = $this->commandHandlerLocator->getHandlerFor($command);
            $commandHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new CommandHandlerFailedMessage($command, $e));
        }
    }
}
