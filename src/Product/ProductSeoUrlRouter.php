<?php

namespace Brera\Product;

use Brera\Http\HttpRouter;
use Brera\KeyValue\DataPoolReader;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\MasterFactory;

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
