<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

interface CommandHandlerFactory
{
    public function createUpdateContentBlockCommandHandler(): CommandHandler;

    public function createUpdateProductCommandHandler(): CommandHandler;

    public function createAddProductListingCommandHandler(): CommandHandler;

    public function createAddImageCommandHandler(): CommandHandler;
    
    public function createShutdownWorkerCommandHandler(): CommandHandler;

    public function createImportCatalogCommandHandler(): CommandHandler;
    
    public function createSetCurrentDataVersionCommandHandler(): CommandHandler;
    
    public function createUpdateTemplateCommandHandler(): CommandHandler;
}
