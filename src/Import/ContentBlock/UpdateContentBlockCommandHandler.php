<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateContentBlockCommandHandler implements CommandHandler
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
        if ($command->getName() !== 'update_content_block_command') {
            $message = sprintf('Expected "update_content_block" command, got "%s"', $command->getName());
            throw new NoUpdateContentBlockCommandMessageException($message);
        }
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $contentBlockSource = ContentBlockSource::rehydrate($this->command->getPayload());
        $contentBlockId = $contentBlockSource->getContentBlockId();

        $payload = json_encode(['id' => (string) $contentBlockId, 'source' => $contentBlockSource->serialize()]);
        $this->domainEventQueue->addNotVersioned('content_block_was_updated', $payload);
    }
}
