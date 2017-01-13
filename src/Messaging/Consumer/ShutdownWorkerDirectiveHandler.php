<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerDirectiveHandler implements CommandHandler, DomainEventHandler
{
    const MAX_RETRIES = 100;

    /**
     * @var ShutdownWorkerDirective
     */
    private $directive;
    
    /**
     * @var EnqueuesMessageEnvelope
     */
    private $enqueuesMessageEnvelope;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Message $message, EnqueuesMessageEnvelope $enqueuesMessageEnvelope, Logger $logger)
    {
        $this->directive = ShutdownWorkerDirective::fromMessage($message);
        $this->enqueuesMessageEnvelope = $enqueuesMessageEnvelope;
        $this->logger = $logger;
    }

    public function process()
    {
        if ($this->isMessageForCurrentProcess()) {
            $this->logger->log(new ConsumerShutdownRequestedLogMessage(getmypid(), $this->directive));
            shutdown();
        }
        $this->addCommandToQueueAgain();
    }

    private function addCommandToQueueAgain()
    {
        $retryCount = $this->directive->getRetryCount() + 1;
        if ($retryCount <= self::MAX_RETRIES) {
            $this->enqueuesMessageEnvelope->add($this->directive->retry());
        }
    }

    private function isMessageForCurrentProcess() : bool
    {
        return '*' === $this->directive->getPid() || getmypid() == $this->directive->getPid();
    }
}
