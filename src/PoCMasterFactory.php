<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method EnvironmentBuilder createEnvironmentBuilder
 * @method EnvironmentBuilder createEnvironmentBuilderWithVersion
 * @method DomainEventConsumer createDomainEventConsumer
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
