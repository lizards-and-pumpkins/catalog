<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class AddImageCommandHandler implements CommandHandler
{
    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(DomainEventQueue $domainEventQueue)
    {
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process(Message $message): void
    {
        $command = AddImageCommand::fromMessage($message);
        $event = new ImageWasAddedDomainEvent($command->getImageFilePath(), $command->getDataVersion());
        $this->domainEventQueue->add($event);
    }
}
