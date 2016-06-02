<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
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

    public function __construct(Message $command, DomainEventQueue $domainEventQueue)
    {
        $this->command = UpdateContentBlockCommand::fromMessage($command);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $contentBlockSource = $this->command->getContentBlockSource();
        $contentBlockId = $contentBlockSource->getContentBlockId();

        $payload = json_encode(['id' => (string) $contentBlockId, 'source' => $contentBlockSource->serialize()]);
        $this->domainEventQueue->addNotVersioned('content_block_was_updated', $payload);
    }
}
