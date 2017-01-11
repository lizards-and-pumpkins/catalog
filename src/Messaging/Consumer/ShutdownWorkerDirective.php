<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Consumer\Exception\InvalidMessageConsumerPidException;
use LizardsAndPumpkins\Messaging\Consumer\Exception\NotShutdownWorkerDirectiveMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerDirective implements Command, DomainEvent
{
    const CODE = 'shutdown_worker';

    /**
     * @var string
     */
    private $pid;

    /**
     * @var int
     */
    private $retryCount;

    public function __construct(string $consumerPid, int $retryCount = 0)
    {
        $this->validateConsumerPid($consumerPid);
        $this->pid = $consumerPid;
        $this->retryCount = $retryCount;
    }

    public function toMessage() : Message
    {
        $name = self::CODE;
        $payload = ['pid' => $this->pid, 'retry_count' => $this->retryCount];
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message) : ShutdownWorkerDirective
    {
        if ($message->getName() !== self::CODE) {
            $format = 'Unable to rehydrate from "%s" queue message, expected "%s"';
            throw new NotShutdownWorkerDirectiveMessageException(sprintf($format, $message->getName(), self::CODE));
        }
        return new self($message->getPayload()['pid'], $message->getPayload()['retry_count']);
    }

    public function getPid() : string
    {
        return $this->pid;
    }

    public function getRetryCount() : int
    {
        return $this->retryCount;
    }

    private function validateConsumerPid(string $consumerPid)
    {
        if (!preg_match('/^(?:[1-9]\d*|\*)$/', $consumerPid)) {
            $msg = sprintf('The consumer PID has to be digits or "*" for any, got "%s"', $consumerPid);
            throw new InvalidMessageConsumerPidException($msg);
        }
    }

    public function retry() : ShutdownWorkerDirective
    {
        return new self($this->getPid(), $this->getRetryCount() + 1);
    }
}
