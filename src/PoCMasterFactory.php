<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;

/**
 * @method KeyValue\DataPoolWriter createDataPoolWriter
 * @method KeyValue\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method EnvironmentBuilder createEnvironmentBuilder
 * @method EnvironmentBuilder createEnvironmentBuilderWithVersion
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
