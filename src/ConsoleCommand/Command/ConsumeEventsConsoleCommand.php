<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ConsumeEventsConsoleCommand implements ConsoleCommand
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
        $this->masterFactory->register(new UpdatingProductImportCommandFactory());
        $this->masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $this->masterFactory->register(new UpdatingProductListingImportCommandFactory());
    }

    public function run()
    {
        /** @var DomainEventConsumer $eventConsumer */
        $eventConsumer = $this->masterFactory->createDomainEventConsumer();
        $eventConsumer->processAll();
    }
}
