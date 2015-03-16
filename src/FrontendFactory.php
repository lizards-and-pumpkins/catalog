<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Product\CatalogImportApiRequestHandler;

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
     * @return UrlKeyRequestHandlerBuilder
     */
    private function createUrlKeyRequestHandlerBuilder()
    {
        return new UrlKeyRequestHandlerBuilder(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getLogger()
        );
    }
}
