<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainCommandHandler;

class ProjectProductStockQuantitySnippetDomainCommandHandler implements DomainCommandHandler
{
    /**
     * @var ProjectProductStockQuantitySnippetDomainCommand
     */
    private $domainCommand;

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
        ProjectProductStockQuantitySnippetDomainCommand $domainCommand,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder,
        ContextSource $contextSource,
        ProductStockQuantityProjector $projector
    ) {
        $this->domainCommand = $domainCommand;
        $this->productStockQuantitySourceBuilder = $productStockQuantitySourceBuilder;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productStockQuantitySource = $this->productStockQuantitySourceBuilder->createFromXml(
            $this->domainCommand->getPayload()
        );

        $this->projector->project($productStockQuantitySource, $this->contextSource);
    }
}
