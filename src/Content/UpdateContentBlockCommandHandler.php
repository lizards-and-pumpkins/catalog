<?php

namespace Brera\Content;

use Brera\CommandHandler;
use Brera\Queue\Queue;

class UpdateContentBlockCommandHandler implements CommandHandler
{
    /**
     * @var UpdateContentBlockCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateContentBlockCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $contentBlockSource = $this->command->getContentBlockSource();
        $contentBlockId = $contentBlockSource->getContentBlockId();

        $this->domainEventQueue->add(new ContentBlockWasUpdatedDomainEvent($contentBlockId, $contentBlockSource));
    }
}
