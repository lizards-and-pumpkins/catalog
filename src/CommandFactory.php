<?php

namespace Brera;

use Brera\Product\ProjectProductStockQuantitySnippetCommand;
use Brera\Product\ProjectProductStockQuantitySnippetCommandHandler;

interface CommandFactory
{
    /**
     * @param ProjectProductStockQuantitySnippetCommand $command
     * @return ProjectProductStockQuantitySnippetCommandHandler
     */
    public function createProjectProductStockQuantitySnippetCommandHandler(
        ProjectProductStockQuantitySnippetCommand $command
    );
}
