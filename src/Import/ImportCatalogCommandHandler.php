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

    /**
     * @var ImportCatalogCommand
     */
    private $importCatalogCommand;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->importCatalogCommand = ImportCatalogCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $domainEvent = new CatalogImportWasTriggeredDomainEvent(
            $this->importCatalogCommand->getDataVersion(),
            $this->importCatalogCommand->getCatalogDataFile()
        );
        $this->domainEventQueue->add($domainEvent);
    }
}
