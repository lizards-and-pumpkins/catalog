<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Queue;

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
        $dataVersion = $this->command->getDataVersion();
        $this->domainEventQueue->add(new ImageWasAddedDomainEvent($imageFilePath, $dataVersion));
    }
}
