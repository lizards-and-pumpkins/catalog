<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Environment\Environment;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportApiRequestHandler;
use Brera\Product\ProductId;
use Brera\Product\ProductSeoUrlRouter;
use Brera\Product\ProductDetailHtmlPage;

class FrontendFactory implements Factory
{
    use FactoryTrait;

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
        $requestHandlerChain->register(
            'catalog_import',
            $this->getMasterFactory()->createCatalogImportApiRequestHandler()
        );
    }

    /**
     * @return CatalogImportApiRequestHandler
     */
    public function createCatalogImportApiRequestHandler()
    {
        return new CatalogImportApiRequestHandler();
    }

    /**
     * @return UrlKeyRouter
     */
    public function createUrlKeyRouter()
    {
        return new UrlKeyRouter($this->createUrlKeyRequestHandlerBuilder());
    }

    /**
     * @param Environment $environment
     * @return PageKeyGenerator
     */
    public function createPageKeyGenerator(Environment $environment)
    {
        return new PageKeyGenerator($environment);
    }

    private function createUrlKeyRequestHandlerBuilder()
    {
        return new UrlKeyRequestHandlerBuilder(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->createDataPoolReader()
        );
    }
}
