<?php

namespace Brera;

use Brera\Content\UpdateContentBlockCommand;
use Brera\Content\UpdateContentBlockCommandHandler;
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

    /**
     * @param UpdateContentBlockCommand $command
     * @return UpdateContentBlockCommandHandler
     */
    public function createUpdateContentBlockCommandHandler(UpdateContentBlockCommand $command);
}
