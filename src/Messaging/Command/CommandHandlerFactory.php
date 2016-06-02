<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandHandlerFactory
{
    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateContentBlockCommandHandler(Message $message);

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateProductCommandHandler(Message $message);

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddProductListingCommandHandler(Message $message);

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddImageCommandHandler(Message $message);
}
