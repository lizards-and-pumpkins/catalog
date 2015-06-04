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

    public function __construct(
        ProductListingCriteriaSnippetRenderer $productListingPageMetaInfoSnippetRenderer,
        DataPoolWriter $dataPoolWriter
    ) {
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

    private function projectProductListing(ProductListingSource $productListingSource)
    {
        $snippet = $this->productListingPageMetaInfoSnippetRenderer->render($productListingSource);

        $this->dataPoolWriter->writeSnippet($snippet);
    }
}
