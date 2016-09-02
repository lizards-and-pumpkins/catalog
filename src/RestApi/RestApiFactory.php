<?php

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
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

    /**
     * @return ApiRouter
     */
    public function createApiRouter()
    {
        $requestHandlerLocator = $this->getApiRequestHandlerLocator();
        $this->registerApiRequestHandlers($requestHandlerLocator);

        return new ApiRouter($requestHandlerLocator);
    }

    private function registerApiRequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $this->registerApiV1RequestHandlers($requestHandlerLocator);
    }

    private function registerApiV1RequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $version = 1;

        $requestHandlerLocator->register(
            'put_catalog_import',
            $version,
            $this->getMasterFactory()->createCatalogImportApiV1PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_content_blocks',
            $version,
            $this->getMasterFactory()->createContentBlocksApiV1PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_templates',
            $version,
            $this->getMasterFactory()->createTemplatesApiV1PutRequestHandler()
        );
    }

    /**
     * @return CatalogImportApiV1PutRequestHandler
     */
    public function createCatalogImportApiV1PutRequestHandler()
    {
        return CatalogImportApiV1PutRequestHandler::create(
            $this->getMasterFactory()->createCatalogImport(),
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return ContentBlocksApiV1PutRequestHandler
     */
    public function createContentBlocksApiV1PutRequestHandler()
    {
        return new ContentBlocksApiV1PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue()
        );
    }

    /**
     * @return TemplatesApiV1PutRequestHandler
     */
    public function createTemplatesApiV1PutRequestHandler()
    {
        return new TemplatesApiV1PutRequestHandler(
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @return string
     */
    private function getCatalogImportDirectoryConfig()
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();
        $catalogImportDirectory = $configReader->get('catalog_import_directory');

        if (null === $catalogImportDirectory) {
            return __DIR__ . '/../../tests/shared-fixture';
        }

        return $catalogImportDirectory;
    }

    /**
     * @return ApiRequestHandlerLocator
     */
    public function getApiRequestHandlerLocator()
    {
        if (null === $this->memoizedApiRequestHandlerLocator) {
            $this->memoizedApiRequestHandlerLocator = new ApiRequestHandlerLocator();
        }

        return $this->memoizedApiRequestHandlerLocator;
    }
}
