<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

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
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @param ProductImportDomainEvent $event
     * @param ProductSourceBuilder $productSourceBuilder
     * @param ContextSource $contextSource
     * @param ProductProjector $projector
     */
    public function __construct(
        ProductImportDomainEvent $event,
        ProductSourceBuilder $productSourceBuilder,
        ContextSource $contextSource,
        ProductProjector $projector
    ) {
        $this->event = $event;
        $this->productSourceBuilder = $productSourceBuilder;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $xml = $this->event->getXml();
        $productSource = $this->productSourceBuilder->createProductSourceFromXml($xml);

        $this->projector->project($productSource, $this->contextSource);
    }
}
