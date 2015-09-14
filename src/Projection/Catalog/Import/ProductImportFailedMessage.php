<?php

namespace Brera\Projection\Catalog\Import;

use Brera\Log\LogMessage;
use Brera\Product\ProductId;

class ProductImportFailedMessage implements LogMessage
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(ProductId $productId, \Exception $exception)
    {
        $this->productId = $productId;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failed to import product ID: %s due to following reason:\n%s",
            $this->productId,
            $this->exception->getMessage()
        );
    }

    /**
     * @return ProductId
     */
    public function getContext()
    {
        return $this->productId;
    }
}
