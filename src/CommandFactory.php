<?php

namespace Brera;

use Brera\Content\UpdateContentBlockCommand;
use Brera\Content\UpdateContentBlockCommandHandler;
use Brera\Image\UpdateImageCommand;
use Brera\Image\UpdateImageCommandHandler;
use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductCommand;
use Brera\Product\UpdateProductCommandHandler;
use Brera\Product\UpdateProductListingCommand;
use Brera\Product\UpdateProductListingCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommand;
use Brera\Product\UpdateProductStockQuantityCommandHandler;

interface CommandFactory
{
    /**
     * @param UpdateProductStockQuantityCommand $command
     * @return UpdateProductStockQuantityCommandHandler
     */
    public function createUpdateProductStockQuantityCommandHandler(
        UpdateProductStockQuantityCommand $command
    );

    /**
     * @param UpdateMultipleProductStockQuantityCommand $command
     * @return UpdateMultipleProductStockQuantityCommandHandler
     */
    public function createUpdateMultipleProductStockQuantityCommandHandler(
        UpdateMultipleProductStockQuantityCommand $command
    );

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
     * @param UpdateProductListingCommand $command
     * @return UpdateProductListingCommandHandler
     */
    public function createUpdateProductListingCommandHandler(UpdateProductListingCommand $command);

    /**
     * @param UpdateImageCommand $command
     * @return UpdateImageCommandHandler
     */
    public function createUpdateImageCommandHandler(UpdateImageCommand $command);
}
