<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Environment\EnvironmentSourceBuilder;

class ProductImportDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductImportDomainEvent
     */
    private $event;

    /**
     * @var ProductSourceBuilder
     */
    private $productSourceBuilder;

    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var ProductSearchDocumentBuilder
     */
    private $searchIndexer;

    /**
     * @var EnvironmentSourceBuilder
     */
    private $environmentSourceBuilder;

    public function __construct(
        ProductImportDomainEvent $event,
        ProductSourceBuilder $productSourceBuilder,
        EnvironmentSourceBuilder $environmentSourceBuilder,
        ProductProjector $projector,
        ProductSearchDocumentBuilder $searchIndexer
    ) {
        $this->event = $event;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->environmentSourceBuilder = $environmentSourceBuilder;
        $this->projector = $projector;
        $this->searchIndexer = $searchIndexer;
    }

    /**
     * @return null
     */
    public function process()
    {
        $xml = $this->event->getXml();
        $productSource = $this->productSourceBuilder->createProductSourceFromXml($xml);
        $environmentSource = $this->environmentSourceBuilder->createFromXml($xml);

        $this->projector->project($productSource, $environmentSource);
        $this->searchIndexer->aggregate($productSource, $environmentSource);
    }
}
