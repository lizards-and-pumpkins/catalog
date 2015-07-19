<?php

namespace Brera;

use Brera\Product\ProductSnippetKeyGenerator;

/**
 * @method DataPool\DataPoolWriter createDataPoolWriter
 * @method DataPool\DataPoolReader createDataPoolReader
 * @method Queue\Queue getCommandQueue
 * @method Queue\Queue getEventQueue
 * @method Context\ContextBuilder createContextBuilder
 * @method Context\ContextBuilder createContextBuilderWithVersion
 * @method Context\ContextSource createContextSource
 * @method DomainEventConsumer createDomainEventConsumer
 * @method CommandConsumer createCommandConsumer
 * @method SnippetKeyGeneratorLocator getSnippetKeyGeneratorLocator
 * @method InMemoryLogger getLogger
 * @method ProductSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method ProductSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockSnippetKeyGenerator
 * @method string[] getRequiredContexts
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
