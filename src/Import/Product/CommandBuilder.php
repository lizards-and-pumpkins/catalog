<?php
namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandBuilder
{
    /**
     * @param Message $message
     * @return Command
     */
    public function fromMessage(Message $message);
}
