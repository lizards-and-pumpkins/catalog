<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

class UpdateProductStockQuantityCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductStockQuantityCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    /**
     * @var ProductStockQuantitySourceBuilder
     */
    private $productStockQuantitySourceBuilder;

    public function __construct(
        UpdateProductStockQuantityCommand $command,
        Queue $domainEventQueue,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder
    ) {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
        $this->productStockQuantitySourceBuilder = $productStockQuantitySourceBuilder;
    }

    public function process()
    {
        $productStockQuantitySource = $this->extractProductStockQuantitySourceFromPayload();

        $productSku = $productStockQuantitySource->getSku();
        $productId = ProductId::fromSku($productSku);

        $event = new ProductStockQuantityUpdatedDomainEvent($productId, $productStockQuantitySource);

        $this->domainEventQueue->add($event);
    }

    /**
     * @return ProductStockQuantitySource
     */
    private function extractProductStockQuantitySourceFromPayload()
    {
        $xml = $this->command->getPayload();
        $productStockQuantitySource = $this->productStockQuantitySourceBuilder->createFromXml($xml);

        return $productStockQuantitySource;
    }
}
