<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Import\Image\AddImageCommand;
use LizardsAndPumpkins\Import\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler;

interface CommandHandlerFactory
{
    /**
     * @param UpdateContentBlockCommand $command
     * @return UpdateContentBlockCommandHandler
     */
    public function createUpdateContentBlockCommandHandler(UpdateContentBlockCommand $command);

    /**
     * @param UpdateProductCommand $command
     * @return UpdateProductCommandHandler
     */
    public function createUpdateProductCommandHandler(UpdateProductCommand $command);

    /**
     * @param AddProductListingCommand $command
     * @return AddProductListingCommandHandler
     */
    public function createAddProductListingCommandHandler(AddProductListingCommand $command);

    /**
     * @param AddImageCommand $command
     * @return AddImageCommandHandler
     */
    public function createAddImageCommandHandler(AddImageCommand $command);
}
