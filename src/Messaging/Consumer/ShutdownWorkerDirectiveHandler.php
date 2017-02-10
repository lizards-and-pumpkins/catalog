<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerDirectiveHandler implements CommandHandler, DomainEventHandler
{
    const MAX_RETRIES = 100;

    /**
     * @var EnqueuesMessageEnvelope
     */
    private $enqueuesMessageEnvelope;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EnqueuesMessageEnvelope $enqueuesMessageEnvelope, Logger $logger)
    {
        $this->enqueuesMessageEnvelope = $enqueuesMessageEnvelope;
        $this->logger = $logger;
    }

    public function process(Message $message)
    {
        $directive = ShutdownWorkerDirective::fromMessage($message);
        if ($this->isMessageForCurrentProcess($directive)) {
            $this->logger->log(new ConsumerShutdownRequestedLogMessage(getmypid(), $directive));
            shutdown();
        }
        $this->addCommandToQueueAgain($directive);
    }

    private function addCommandToQueueAgain(ShutdownWorkerDirective $directive)
    {
        $retryCount = $directive->getRetryCount() + 1;
        if ($retryCount <= self::MAX_RETRIES) {
            $this->enqueuesMessageEnvelope->add($directive->retry());
        }
    }

    private function isMessageForCurrentProcess(ShutdownWorkerDirective $directive) : bool
    {
        return '*' === $directive->getPid() || getmypid() == $directive->getPid();
    }
}
