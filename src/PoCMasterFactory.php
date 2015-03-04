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
 * @method AbstractLogger getLogger
 * @method Product\ProductDetailViewSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createGenericSnippetKeyGenerator
 */
class PoCMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
