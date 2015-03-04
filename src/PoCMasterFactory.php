<?php

namespace Brera;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getEventQueue
 * @method InMemoryLogger getLogger
 * @method Context\ContextBuilder createContextBuilder
 * @method Context\ContextBuilder createContextBuilderWithVersion
 * @method Context\ContextSourceBuilder createContextSourceBuilder
 * @method DomainEventConsumer createDomainEventConsumer
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method Logger getLogger
 * @method ContextSourceBuilder createContextSourceBuilder
 * @method Product\ProductDetailViewSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
