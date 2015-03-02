<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Context\ContextSourceBuilder;

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
     * @var ContextSourceBuilder
     */
    private $contextSourceBuilder;

    /**
     * @param ProductImportDomainEvent $event
     * @param ProductSourceBuilder $productSourceBuilder
     * @param ContextSourceBuilder $contextSourceBuilder
     * @param ProductProjector $projector
     */
    public function __construct(
        ProductImportDomainEvent $event,
        ProductSourceBuilder $productSourceBuilder,
        ContextSourceBuilder $contextSourceBuilder,
        ProductProjector $projector
    ) {
        $this->event = $event;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->contextSourceBuilder = $contextSourceBuilder;
        $this->projector = $projector;
    }

    public function process()
    {
        $xml = $this->event->getXml();
        $productSource = $this->productSourceBuilder->createProductSourceFromXml($xml);
        $contextSource = $this->contextSourceBuilder->createFromXml($xml);

        $this->projector->project($productSource, $contextSource);
    }
}
