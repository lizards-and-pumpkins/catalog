<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerCommandHandler implements CommandHandler
{
    const MAX_RETRIES = 100;
    
    /**
     * @var ShutdownWorkerCommand
     */
    private $command;

    /**
     * @var CommandQueue
     */
    private $commandQueue;

    public function __construct(Message $message, CommandQueue $commandQueue)
    {
        $this->command = ShutdownWorkerCommand::fromMessage($message);
        $this->commandQueue = $commandQueue;
    }

    public function process()
    {
        if ($this->isMatchingCurrentProcess($this->command->getPid())) {
            shutdown();
        }
        $this->addCommandToQueueAgain();
    }

    private function addCommandToQueueAgain()
    {
        $retryCount = $this->command->getRetryCount() + 1;
        if ($retryCount <= self::MAX_RETRIES) {
            $this->commandQueue->add(new ShutdownWorkerCommand($this->command->getPid(), $retryCount));
        }
    }

    private function isMatchingCurrentProcess(string $pid): bool
    {
        return $pid === '*' || $pid == getmypid();
    }
}
