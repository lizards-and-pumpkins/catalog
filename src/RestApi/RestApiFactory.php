<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class RestApiFactory implements Factory
{
    use FactoryTrait;

    /**
     * @var ApiRequestHandlerLocator
     */
    private $memoizedApiRequestHandlerLocator;

    public function createApiRouter() : ApiRouter
    {
        $requestHandlerLocator = $this->getApiRequestHandlerLocator();
        $this->registerApiRequestHandlers($requestHandlerLocator);

        return new ApiRouter($requestHandlerLocator);
    }

    private function registerApiRequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $this->registerApiV1RequestHandlers($requestHandlerLocator);
    }

    final protected function registerApiV1RequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $requestHandlerLocator->register(
            'put_catalog_import',
            $version = 1,
            $this->getMasterFactory()->createCatalogImportApiV1PutRequestHandler()
        );
        
        $requestHandlerLocator->register(
            'put_catalog_import',
            $version = 2,
            $this->getMasterFactory()->createCatalogImportApiV2PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_content_blocks',
            $version = 1,
            $this->getMasterFactory()->createContentBlocksApiV1PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_templates',
            $version = 1,
            $this->getMasterFactory()->createTemplatesApiV1PutRequestHandler()
        );
    }

    public function createCatalogImportApiV1PutRequestHandler() : CatalogImportApiV1PutRequestHandler
    {
        return CatalogImportApiV1PutRequestHandler::create(
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createCatalogImportApiV2PutRequestHandler(): CatalogImportApiV2PutRequestHandler
    {
        return CatalogImportApiV2PutRequestHandler::create(
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createContentBlocksApiV1PutRequestHandler() : ContentBlocksApiV1PutRequestHandler
    {
        return new ContentBlocksApiV1PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue()
        );
    }

    public function createTemplatesApiV1PutRequestHandler() : TemplatesApiV1PutRequestHandler
    {
        return new TemplatesApiV1PutRequestHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    private function getCatalogImportDirectoryConfig() : string
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('catalog_import_directory');
    }

    public function getApiRequestHandlerLocator() : ApiRequestHandlerLocator
    {
        if (null === $this->memoizedApiRequestHandlerLocator) {
            $this->memoizedApiRequestHandlerLocator = new ApiRequestHandlerLocator();
        }

        return $this->memoizedApiRequestHandlerLocator;
    }
}
