<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

class ProductWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductWasUpdatedDomainEvent
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

    public function __construct(
        ProductWasUpdatedDomainEvent $event,
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
