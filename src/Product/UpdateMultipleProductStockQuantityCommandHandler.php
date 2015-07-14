<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;
use Brera\Utils\XPathParser;

class UpdateMultipleProductStockQuantityCommandHandler implements CommandHandler
{
    /**
     * @var UpdateMultipleProductStockQuantityCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $commandQueue;

    public function __construct(UpdateMultipleProductStockQuantityCommand $command, Queue $commandQueue)
    {
        $this->command = $command;
        $this->commandQueue = $commandQueue;
    }

    public function process()
    {
        $xml = $this->command->getPayload();
        $this->emitUpdateProductStockQuantityCommands($xml);
    }

    /**
     * @param string $xml
     */
    private function emitUpdateProductStockQuantityCommands($xml)
    {
        $stockNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('/*/stock');
        foreach ($stockNodesXml as $stockXml) {
            $this->commandQueue->add(new UpdateProductStockQuantityCommand($stockXml));
        }
    }
}
