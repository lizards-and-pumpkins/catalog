<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;
use Brera\PoC\KeyValue\DataPoolReader;
use Brera\PoC\Http\HttpResponse;
use Brera\PoC\Http\HttpRequestHandler;

class ProductDetailHtmlPage implements HttpRequestHandler
{
    /**
     * @var ProductId
     */
    private $productId;
    
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @param ProductId $productId
     * @param DataPoolReader $dataPoolReader
     */
    public function __construct(ProductId $productId, DataPoolReader $dataPoolReader)
    {
        $this->productId = $productId;
        $this->dataPoolReader = $dataPoolReader;
    }

    /**
     * @return HttpResponse
     */
    public function process()
    {
        $html = $this->dataPoolReader->getPoCProductHtml($this->productId);
        return $html;
    }
} 
