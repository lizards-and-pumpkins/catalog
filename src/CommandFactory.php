<?php

namespace Brera;

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
}
