<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandHandlerFactory
{
    public function createUpdateContentBlockCommandHandler(Message $command): CommandHandler;

    public function createUpdateProductCommandHandler(Message $command): CommandHandler;

    public function createAddProductListingCommandHandler(Message $command): CommandHandler;

    public function createAddImageCommandHandler(Message $command): CommandHandler;
}
