<?php

namespace Brera;

class ProductSearchTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }
    
    public function testProductSearchResultsMetaSnippetIsWrittenIntoDataPool()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $queue = $this->factory->getEventQueue();
        $queue->add(new RootTemplateChangedDomainEvent($xml));

        $this->processDomainEvents();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $metaInfoSnippetKeyGenerator = $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($context, []);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);

        $expectedMetaInfoContent = [
            'root_snippet_code'  => 'product_listing',
            'page_snippet_codes' => [
                'product_listing',
                'global_notices',
                'breadcrumbsContainer',
                'global_messages',
                'before_body_end'
            ]
        ];

        $this->assertSame(json_encode($expectedMetaInfoContent), $metaInfoSnippet);
    }

    private function processDomainEvents()
    {
        $queue = $this->factory->getEventQueue();
        $consumer = $this->factory->createDomainEventConsumer();

        while ($queue->count() > 0) {
            $consumer->process(1);
        }

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);
    }
}
