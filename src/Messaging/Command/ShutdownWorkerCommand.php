<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\InvalidCommandConsumerPidException;
use LizardsAndPumpkins\Messaging\Command\Exception\NoShutdownWorkerCommandMessageException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerCommand implements Command
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

    public function __construct(string $commandConsumerPid, int $retryCount = 0)
    {
        $this->validateCommandConsumerPid($commandConsumerPid);
        $this->pid = $commandConsumerPid;
        $this->retryCount = $retryCount;
    }

    public function toMessage() : Message
    {
        $name = self::CODE;
        $payload = ['pid' => $this->pid, 'retry_count' => $this->retryCount];
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message) : ShutdownWorkerCommand
    {
        if ($message->getName() !== self::CODE) {
            $format = 'Unable to rehydrate command from "%s" queue message, expected "%s"';
            throw new NoShutdownWorkerCommandMessageException(sprintf($format, $message->getName(), self::CODE));
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

    private function validateCommandConsumerPid(string $commandConsumerPid)
    {
        if (!preg_match('/^(?:[1-9]\d*|\*)$/', $commandConsumerPid)) {
            $msg = sprintf('The command consumer PID has to be digits or "*" for any, got "%s"', $commandConsumerPid);
            throw new InvalidCommandConsumerPidException($msg);
        }
    }
}
