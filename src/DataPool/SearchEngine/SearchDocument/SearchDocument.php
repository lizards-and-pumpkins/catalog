<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductId;

class SearchDocument
{
    /**
     * @var SearchDocumentFieldCollection
     */
    private $fields;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductId
     */
    private $productId;

    public function __construct(SearchDocumentFieldCollection $fields, Context $context, ProductId $productId)
    {
        $this->fields = $fields;
        $this->context = $context;
        $this->productId = $productId;
    }

    /**
     * @return SearchDocumentFieldCollection
     */
    public function getFieldsCollection()
    {
        return $this->fields;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->productId;
    }
}
