<?php


namespace Brera\PoC;

/**
 * Interface PoCMasterFactory
 * @package Brera\PoC
 *
 * @method DataPoolWriter createDataPoolWriter
 * @method DataPoolReader createDataPoolReader
 * @method ProductSeoUrlRouter createProductSeoUrlRouter
 * @method ProductRepository getProductRepository
 * @method DomainEventQueue getEventQueue
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
} 
