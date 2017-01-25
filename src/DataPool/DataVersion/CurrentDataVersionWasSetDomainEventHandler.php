<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

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

    public function __construct(Message $message, DataPoolWriter $dataPoolWriter)
    {
        $this->event = CurrentDataVersionWasSetDomainEvent::fromMessage($message);
        $this->dataPoolWriter = $dataPoolWriter;
    }

    public function process()
    {
        $this->dataPoolWriter->setCurrentDataVersion((string) $this->event->getDataVersion());
    }
}
