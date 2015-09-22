<?php

namespace LizardsAndPumpkins\Image;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

class AddImageCommandHandler implements CommandHandler
{
    /**
     * @var AddImageCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(AddImageCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $imageFilePath = $this->command->getImageFilePath();
        $this->domainEventQueue->add(new ImageWasAddedDomainEvent($imageFilePath));
    }
}
