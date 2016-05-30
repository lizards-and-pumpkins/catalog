<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommandHandler implements CommandHandler
{
    /**
     * @var Message
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $command, DomainEventQueue $domainEventQueue)
    {
        if ($command->getName() !== 'update_product_command') {
            $message = sprintf('Expected "update_product" command, got "%s"', $command->getName());
            throw new NoUpdateProductCommandMessageException($message);
        }
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $payload = json_decode($this->command->getPayload(), true);
        // todo: encapsulate product serialization and rehydration
        $product = $payload['product'][Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE ?
            ConfigurableProduct::fromArray($payload['product']) :
            SimpleProduct::fromArray($payload['product']);
        $version = DataVersion::fromVersionString($product->getContext()->getValue(DataVersion::CONTEXT_CODE));
        $payload = json_encode(['id' => $product->getId(), 'product' => $product]);
        $this->domainEventQueue->addVersioned('product_was_updated', $payload, $version);
    }
}
