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
     * @var ProductListingSourceListBuilder
     */
    private $productListingSourceListBuilder;

    public function __construct(
        SnippetRendererCollection $snippetRendererCollection,
        DataPoolWriter $dataPoolWriter,
        ProductListingSourceListBuilder $productListingSourceListBuilder
    ) {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->productListingSourceListBuilder = $productListingSourceListBuilder;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        $productListingSourceList = $this->productListingSourceListBuilder->fromJson($projectionSourceData);
        $snippetList = $this->snippetRendererCollection->render($productListingSourceList, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
