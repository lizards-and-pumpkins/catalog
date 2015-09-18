<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

class ProductListingTemplateProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    /**
     * @var ProductsPerPageForContextListBuilder
     */
    private $productsPerPageForContextListBuilder;

    public function __construct(
        SnippetRendererCollection $snippetRendererCollection,
        DataPoolWriter $dataPoolWriter,
        ProductsPerPageForContextListBuilder $productsPerPageForContextListBuilder
    ) {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->productsPerPageForContextListBuilder = $productsPerPageForContextListBuilder;
    }

    /**
     * @param mixed $productsPerPageSourceData
     * @param ContextSource $contextSource
     */
    public function project($productsPerPageSourceData, ContextSource $contextSource)
    {
        $productsPerPageList = $this->productsPerPageForContextListBuilder->fromJson($productsPerPageSourceData);
        $snippetList = $this->snippetRendererCollection->render($productsPerPageList, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
