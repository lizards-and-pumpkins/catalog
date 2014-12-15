<?php

namespace Brera\PoC;

/**
 * Interface PoCMasterFactory
 * @package Brera\PoC
 *
 * @method KeyValue\DataPoolWriter createDataPoolWriter
 * @method KeyValue\DataPoolReader createDataPoolReader
 * @method ProductSeoUrlRouter createProductSeoUrlRouter
 * @method Queue\DomainEventQueue getEventQueue
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
} 
