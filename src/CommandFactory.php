<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Image\UpdateImageCommand;
use LizardsAndPumpkins\Image\UpdateImageCommandHandler;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\AddProductListingCommandHandler;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommand;
use LizardsAndPumpkins\Product\UpdateProductStockQuantityCommandHandler;

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
     * @param AddProductListingCommand $command
     * @return AddProductListingCommandHandler
     */
    public function createAddProductListingCommandHandler(AddProductListingCommand $command);

    /**
     * @param UpdateImageCommand $command
     * @return UpdateImageCommandHandler
     */
    public function createUpdateImageCommandHandler(UpdateImageCommand $command);
}
