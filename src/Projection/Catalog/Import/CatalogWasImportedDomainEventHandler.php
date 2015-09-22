<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\DomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector;

class CatalogWasImportedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;
    
    /**
     * @var ProductListingPageSnippetProjector
     */
    private $listingProjector;

    public function __construct(CatalogWasImportedDomainEvent $event, ProductListingPageSnippetProjector $projection)
    {
        $this->event = $event;
        $this->listingProjector = $projection;
    }

    public function process()
    {
        $version = $this->event->getDataVersion();
        $this->listingProjector->project($version);
    }
}
