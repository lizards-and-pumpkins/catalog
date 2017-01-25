<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CurrentDataVersionWasSetDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CurrentDataVersionWasSetDomainEvent
     */
    private $event;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(Message $message, DataPoolWriter $dataPoolWriter, DataPoolReader $dataPoolReader)
    {
        $this->event = CurrentDataVersionWasSetDomainEvent::fromMessage($message);
        $this->dataPoolWriter = $dataPoolWriter;
        $this->dataPoolReader = $dataPoolReader;
    }

    public function process()
    {
        // Note: NON ATOMIC UPDATE! TEMPORARY SOLUTION UNTIL EVENT SOURCING IS IMPLEMENTED!
        $this->dataPoolWriter->setPreviousDataVersion((string) $this->dataPoolReader->getCurrentDataVersion());
        $this->dataPoolWriter->setCurrentDataVersion((string) $this->event->getDataVersion());
    }
}
