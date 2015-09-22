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
    private $listingProjection;

    public function __construct(CatalogWasImportedDomainEvent $event, ProductListingPageSnippetProjector $projection)
    {
        $this->event = $event;
        $this->listingProjection = $projection;
    }

    public function process()
    {
        $version = $this->event->getDataVersion();
        $this->listingProjection->project($version);
    }
}
