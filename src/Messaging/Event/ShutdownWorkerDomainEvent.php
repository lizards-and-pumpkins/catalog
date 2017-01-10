<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\InvalidDomainEventConsumerPidException;
use LizardsAndPumpkins\Messaging\Event\Exception\NoShutdownWorkerDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerDomainEvent implements DomainEvent
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

    public function __construct(string $eventConsumerPid, int $retryCount = 0)
    {
        if (! preg_match('/^(?:[1-9]\d*|\*)$/', $eventConsumerPid)) {
            $msg = sprintf('The event consumer PID has to be numeric or "*" for all, got "%s"', $eventConsumerPid);
            throw new InvalidDomainEventConsumerPidException($msg);
        }
        $this->pid = $eventConsumerPid;
        $this->retryCount = $retryCount;
    }

    public function toMessage(): Message
    {
        $payload = ['pid' => $this->pid, 'retry_count' => $this->retryCount];
        $metadata = [];

        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    public static function fromMessage(Message $message): ShutdownWorkerDomainEvent
    {
        if ($message->getName() !== self::CODE) {
            $format = 'Unable to rehydrate event from "%s" queue message, expected "%s"';
            throw new NoShutdownWorkerDomainEventMessageException(sprintf($format, $message->getName(), self::CODE));
        }
        return new self($message->getPayload()['pid'], $message->getPayload()['retry_count']);
    }

    public function getPid(): string
    {
        return $this->pid;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }
}
