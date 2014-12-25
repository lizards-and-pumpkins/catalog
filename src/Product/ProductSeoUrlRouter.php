<?php

namespace Brera\PoC\Product;

use Brera\PoC\Http\HttpRouter;
use Brera\PoC\KeyValue\DataPoolReader;
use Brera\PoC\Http\HttpRequest;
use Brera\PoC\Http\HttpRequestHandler;
use Brera\PoC\MasterFactory;

class ProductSeoUrlRouter implements HttpRouter
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var MasterFactory
     */
    private $factory;

    /**
     * @param DataPoolReader $dataPoolReader
     * @param MasterFactory $factory
     */
    public function __construct(DataPoolReader $dataPoolReader, MasterFactory $factory)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->factory = $factory;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        if (!$this->dataPoolReader->hasProductSeoUrl($request->getUrl())) {
            return null;
        }

        $productId = $this->dataPoolReader->getProductIdBySeoUrl($request->getUrl());

        return $this->factory->createProductDetailPage($productId);
    }
} 
