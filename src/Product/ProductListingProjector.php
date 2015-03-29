<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\Projector;

class ProductListingProjector implements Projector
{
    /**
     * @var ProductListingCriteriaSnippetRenderer
     */
    private $productListingPageMetaInfoSnippetRenderer;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @param ProductListingCriteriaSnippetRenderer $productListingPageMetaInfoSnippetRenderer
     * @param DataPoolWriter $dataPoolWriter
     */
    public function __construct(
        ProductListingCriteriaSnippetRenderer $productListingPageMetaInfoSnippetRenderer,
        DataPoolWriter $dataPoolWriter
    )
    {
        $this->productListingPageMetaInfoSnippetRenderer = $productListingPageMetaInfoSnippetRenderer;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param ProjectionSourceData $dataObject
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $dataObject)
    {
        if (!($dataObject instanceof ProductListingSource)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of ProductListingSource.'
            );
        }

        $this->projectProductListing($dataObject);
    }

    /**
     * @param ProductListingSource $productListingSource
     */
    private function projectProductListing(ProductListingSource $productListingSource)
    {
        $snippetResult = $this->productListingPageMetaInfoSnippetRenderer->render($productListingSource);

        $this->dataPoolWriter->writeSnippetResult($snippetResult);
    }
}
