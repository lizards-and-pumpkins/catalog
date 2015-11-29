<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;

interface CommandFactory
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
