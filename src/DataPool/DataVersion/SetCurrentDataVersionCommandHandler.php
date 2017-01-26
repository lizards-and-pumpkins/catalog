<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class SetCurrentDataVersionCommandHandler implements CommandHandler
{
    /**
     * @var SetCurrentDataVersionCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    public function __construct(
        Message $message,
        DomainEventQueue $domainEventQueue,
        DataPoolReader $dataPoolReader,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->command = SetCurrentDataVersionCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
        $this->dataPoolReader = $dataPoolReader;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    public function process()
    {
        $newDataVersion = $this->command->getDataVersion();
        
        // Note: NON ATOMIC UPDATE! TEMPORARY SOLUTION UNTIL EVENT SOURCING IS IMPLEMENTED!
        $this->dataPoolWriter->setPreviousDataVersion((string) $this->dataPoolReader->getCurrentDataVersion());
        $this->dataPoolWriter->setCurrentDataVersion((string) $newDataVersion);

        $this->domainEventQueue->add(new CurrentDataVersionWasSetDomainEvent($newDataVersion));
    }
}
