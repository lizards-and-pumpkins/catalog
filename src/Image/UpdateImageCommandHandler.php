<?php

namespace Brera\Image;

use Brera\CommandHandler;
use Brera\Queue\Queue;

class UpdateImageCommandHandler implements CommandHandler
{
    /**
     * @var UpdateImageCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateImageCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $imageFileName = $this->command->getImageFileName();
        $this->domainEventQueue->add(new ImageWasUpdatedDomainEvent($imageFileName));
    }
}
