<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\Projector;
use Brera\SnippetRendererCollection;

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
     * @param ContextSource $context
     */
    public function project($projectionSourceData, ContextSource $context)
    {
        $productListingSourceList = $this->productListingSourceListBuilder->fromJson($projectionSourceData);
        $snippetList = $this->snippetRendererCollection->render($productListingSourceList, $context);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
