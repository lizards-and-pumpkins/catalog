<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;

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

    public function getFieldsCollection() : SearchDocumentFieldCollection
    {
        return $this->fields;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getProductId() : ProductId
    {
        return $this->productId;
    }
}
