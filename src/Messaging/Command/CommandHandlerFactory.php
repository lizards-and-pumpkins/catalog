<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandHandlerFactory
{
    public function createUpdateContentBlockCommandHandler(Message $message) : CommandHandler;

    public function createUpdateProductCommandHandler(Message $message) : CommandHandler;

    public function createAddProductListingCommandHandler(Message $message) : CommandHandler;

    public function createAddImageCommandHandler(Message $message) : CommandHandler;
}
