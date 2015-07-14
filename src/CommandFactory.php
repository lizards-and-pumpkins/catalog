<?php

namespace Brera;

use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
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

}
