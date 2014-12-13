<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductRepository;

class ProductCreatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductCreatedDomainEvent
     */
    private $event;

    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @param ProductCreatedDomainEvent $event
     * @param ProductRepository $repository
     */
    public function __construct(
        ProductCreatedDomainEvent $event,
        ProductRepository $repository,
        ProductProjector $projector
    )
    {
        $this->event = $event;
        $this->repository = $repository;
        $this->projector = $projector;
    }

    /**
     * @return null
     */
    public function process()
    {
        $productId = $this->event->getProductId();
        $product = $this->repository->findById($productId);
        $this->projector->project($product);
    }
} 
