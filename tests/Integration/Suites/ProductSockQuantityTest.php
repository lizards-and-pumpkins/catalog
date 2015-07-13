<?php

namespace Brera;

use Brera\Product\UpdateProductStockQuantityCommand;

class ProductSockQuantityTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }

    public function testProductStockQuantitySnippetIsWrittenIntoDataPool()
    {
        $xml = <<<EOX
<?xml version="1.0"?>
<sockNode website="ru" language="en_US">
    <sku>foo</sku>
    <quantity>200</quantity>
</sockNode>
EOX;
        $queue = $this->factory->getCommandQueue();
        $queue->add(new UpdateProductStockQuantityCommand($xml));

        $this->processCommands(1);
        $this->processDomainEvents(1);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGenerator = $this->factory->createProductStockQuantityRendererSnippetKeyGenerator();
        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => 'foo']);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $result = $dataPoolReader->getSnippet($snippetKey);

        $this->assertEquals(200, $result);
    }

    /**
     * @param int $numberOfMessages
     */
    private function processDomainEvents($numberOfMessages)
    {
        $consumer = $this->factory->createDomainEventConsumer();
        $consumer->process($numberOfMessages);
    }

    /**
     * @param int $numberOfMessages
     */
    private function processCommands($numberOfMessages)
    {
        $consumer = $this->factory->createCommandConsumer();
        $consumer->process($numberOfMessages);
    }
}
