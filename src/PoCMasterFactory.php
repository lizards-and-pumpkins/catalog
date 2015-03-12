<?php

namespace Brera;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method Context\ContextBuilder createContextBuilder
 * @method Context\ContextBuilder createContextBuilderWithVersion
 * @method Context\ContextSource createContextSource
 * @method DomainEventConsumer createDomainEventConsumer
 * @method SnippetKeyGenerator getSnippetKeyGenerator
 * @method InMemoryLogger getLogger
 * @method Product\ProductSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
