<?php

namespace Brera;

use Brera\Context\ContextBuilder;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method ContextBuilder createContextBuilder
 * @method ContextBuilder createContextBuilderWithVersion
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
