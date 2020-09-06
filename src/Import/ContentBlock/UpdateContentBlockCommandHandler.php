<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateContentBlockCommandHandler implements CommandHandler
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
        $command = UpdateContentBlockCommand::fromMessage($message);
        $contentBlockSource = $command->getContentBlockSource();

        $this->domainEventQueue->add(new ContentBlockWasUpdatedDomainEvent($contentBlockSource));
    }
}
