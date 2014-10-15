<?php


namespace Brera\PoC;


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
    
    public function __construct(DataPoolReader $dataPoolReader, MasterFactory $factory)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->factory = $factory;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
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
