<?php

namespace Brera;

/**
 * Interface PoCMasterFactory
 * @package Brera
 *
 * @method KeyValue\DataPoolWriter createDataPoolWriter
 * @method KeyValue\DataPoolReader createDataPoolReader
 * @method Product\ProductSeoUrlRouter createProductSeoUrlRouter
 * @method Queue\Queue getEventQueue
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
} 
