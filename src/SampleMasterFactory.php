<?php

namespace Brera;

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
 * @method GenericSnippetKeyGenerator createProductDetailViewSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductStockQuantityRendererSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductListingMetaDataSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createProductDetailPageMetaSnippetKeyGenerator
 * @method GenericSnippetKeyGenerator createContentBlockInProductListingSnippetKeyGenerator
 * @method string[] getRequiredContexts
 */
class SampleMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
