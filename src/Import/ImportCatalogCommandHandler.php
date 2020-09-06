<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ImportCatalogCommandHandler implements CommandHandler
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
        $importCatalogCommand = ImportCatalogCommand::fromMessage($message);
        $domainEvent = new CatalogImportWasTriggeredDomainEvent(
            $importCatalogCommand->getDataVersion(),
            $importCatalogCommand->getCatalogDataFile()
        );
        $this->domainEventQueue->add($domainEvent);
    }
}
