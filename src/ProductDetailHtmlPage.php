<?php

namespace Brera\PoC;

use Brera\Poc\Product\ProductId,
    Brera\PoC\KeyValue\DataPoolReader,
    Brera\PoC\Http\HttpResponse,
    Brera\PoC\Http\HttpRequestHandler;

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
