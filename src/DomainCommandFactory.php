<?php

namespace Brera;

use Brera\Product\ProjectProductStockQuantitySnippetDomainCommand;
use Brera\Product\ProjectProductStockQuantitySnippetDomainCommandHandler;

interface DomainCommandFactory
{
    /**
     * @param ProjectProductStockQuantitySnippetDomainCommand $command
     * @return ProjectProductStockQuantitySnippetDomainCommandHandler
     */
    public function createProjectProductStockQuantitySnippetDomainCommandHandler(
        ProjectProductStockQuantitySnippetDomainCommand $command
    );
}
