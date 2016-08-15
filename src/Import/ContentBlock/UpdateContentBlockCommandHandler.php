<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateContentBlockCommandHandler implements CommandHandler
{
    /**
     * @var UpdateContentBlockCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = UpdateContentBlockCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $contentBlockSource = $this->command->getContentBlockSource();

        $this->domainEventQueue->add(new ContentBlockWasUpdatedDomainEvent($contentBlockSource));
    }
}
