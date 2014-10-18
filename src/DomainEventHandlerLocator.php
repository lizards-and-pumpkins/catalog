<?php

namespace Brera\PoC;

class DomainEventHandlerLocator
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    /**
     * @param IntegrationTestFactory $factory
     */
    public function __construct(IntegrationTestFactory $factory)
    {
        $this->factory = $factory;
    }
    
    /**
     * @param DomainEvent $event
     * @return ProductCreatedDomainEventHandler
     */
    public function getHandlerFor(DomainEvent $event)
    {
        // todo: switch, throw exception if not found a matching handler
        return $this->factory->createProductCreatedDomainEventHandler($event);
    }
} 
