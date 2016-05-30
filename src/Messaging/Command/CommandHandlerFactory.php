<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandHandlerFactory
{
    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createUpdateContentBlockCommandHandler(Message $command);

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createUpdateProductCommandHandler(Message $command);

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createAddProductListingCommandHandler(Message $command);

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createAddImageCommandHandler(Message $command);
}
