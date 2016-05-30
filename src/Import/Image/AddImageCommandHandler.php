<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class AddImageCommandHandler implements CommandHandler
{
    /**
     * @var Message
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $command, DomainEventQueue $domainEventQueue)
    {
        if ('add_image_command' !== $command->getName()) {
            $message = sprintf('Expected "add_image" command, got "%s"', $command->getName());
            throw new NoAddImageCommandMessageException($message);
        }
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $commandPayload = json_decode($this->command->getPayload(), true);
        $eventPayload = json_encode(['file_path' => $commandPayload['file_path']]);
        $dataVersion = DataVersion::fromVersionString($commandPayload['data_version']);
        $this->domainEventQueue->addVersioned('image_was_added', $eventPayload, $dataVersion);
    }
}
