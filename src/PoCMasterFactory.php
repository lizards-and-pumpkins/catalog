<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSourceBuilder;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method ContextBuilder createContextBuilder
 * @method ContextBuilder createContextBuilderWithVersion
 * @method DomainEventConsumer createDomainEventConsumer
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method Logger getLogger
 * @method ContextSourceBuilder createContextSourceBuilder
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
