<?php

namespace Brera\PoC;

use Brera\PoC\Api\ApiRequestHandlerChain;
use Brera\PoC\Api\ApiRouter;
use Brera\PoC\Product\ProductApiRequestHandler;
use Brera\PoC\Product\ProductId;
use Brera\PoC\Product\ProductSeoUrlRouter;
use Brera\PoC\Product\ProductDetailHtmlPage;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return ProductSeoUrlRouter
     */
    public function createProductSeoUrlRouter()
    {
        return new ProductSeoUrlRouter(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()
        );
    }

    /**
     * @param ProductId $productId
     * @return ProductDetailHtmlPage
     */
    public function createProductDetailPage(ProductId $productId)
    {
        return new ProductDetailHtmlPage(
            $productId,
            $this->getMasterFactory()->createDataPoolReader()
        );
    }

	/**
	 * @return ApiRouter
	 */
	public function createApiRouter()
	{
		$requestHandlerChain = new ApiRequestHandlerChain();
		$this->registerApiRequestHandlers($requestHandlerChain);

		return new ApiRouter($requestHandlerChain);
	}

	/**
	 * @param ApiRequestHandlerChain $requestHandlerChain
	 */
	protected function registerApiRequestHandlers(ApiRequestHandlerChain $requestHandlerChain)
	{
		$requestHandlerChain->register('product', $this->getMasterFactory()->createProductApiRequestHandler());
	}

	/**
	 * @return ProductApiRequestHandler
	 */
	public function createProductApiRequestHandler()
	{
		return new ProductApiRequestHandler();
	}
}
