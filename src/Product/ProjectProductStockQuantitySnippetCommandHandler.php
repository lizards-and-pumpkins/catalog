<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\CommandHandler;

class ProjectProductStockQuantitySnippetCommandHandler implements CommandHandler
{
    /**
     * @var ProjectProductStockQuantitySnippetCommand
     */
    private $command;

    /**
     * @var ProductStockQuantitySourceBuilder
     */
    private $productStockQuantitySourceBuilder;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductStockQuantityProjector
     */
    private $projector;

    public function __construct(
        ProjectProductStockQuantitySnippetCommand $command,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder,
        ContextSource $contextSource,
        ProductStockQuantityProjector $projector
    ) {
        $this->command = $command;
        $this->productStockQuantitySourceBuilder = $productStockQuantitySourceBuilder;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productStockQuantitySource = $this->productStockQuantitySourceBuilder->createFromXml(
            $this->command->getPayload()
        );

        $this->projector->project($productStockQuantitySource, $this->contextSource);
    }
}
