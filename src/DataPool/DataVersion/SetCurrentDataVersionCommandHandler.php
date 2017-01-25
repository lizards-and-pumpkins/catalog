<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class SetCurrentDataVersionCommandHandler implements CommandHandler
{
    /**
     * @var SetCurrentDataVersionCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = SetCurrentDataVersionCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $event = new CurrentDataVersionWasSetDomainEvent($this->command->getDataVersion());
        $this->domainEventQueue->add($event);
    }
}
