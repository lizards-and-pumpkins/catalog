<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\ProductImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\TemplateApiV1GetRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
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

    public function createApiRouter(): ApiRouter
    {
        return new ApiRouter(
            $this->getApiRequestHandlerLocator(),
            $this->getMasterFactory()->createUrlToWebsiteMap()
        );
    }

    public function getApiRequestHandlerLocator(): ApiRequestHandlerLocator
    {
        if (null === $this->memoizedApiRequestHandlerLocator) {
            $this->memoizedApiRequestHandlerLocator = new ApiRequestHandlerLocator();
            $this->registerApiRequestHandlers($this->memoizedApiRequestHandlerLocator);
        }

        return $this->memoizedApiRequestHandlerLocator;
    }

    private function registerApiRequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $requestHandlerLocator->register('put_catalog_import', $version = 1, function () {
            return $this->getMasterFactory()->createCatalogImportApiV1PutRequestHandler();
        });

        $requestHandlerLocator->register('put_catalog_import', $version = 2, function () {
            return $this->getMasterFactory()->createCatalogImportApiV2PutRequestHandler();
        });

        $requestHandlerLocator->register('put_product_import', $version = 1, function () {
            return $this->getMasterFactory()->createProductImportApiV1PutRequestHandler();
        });

        $requestHandlerLocator->register('put_content_blocks', $version = 1, function () {
            return $this->getMasterFactory()->createContentBlocksApiV1PutRequestHandler();
        });

        $requestHandlerLocator->register('put_content_blocks', $version = 2, function () {
            return $this->getMasterFactory()->createContentBlocksApiV2PutRequestHandler();
        });

        $requestHandlerLocator->register('put_templates', $version = 1, function () {
            return $this->getMasterFactory()->createTemplatesApiV1PutRequestHandler();
        });

        $requestHandlerLocator->register('put_templates', $version = 2, function () {
            return $this->getMasterFactory()->createTemplatesApiV2PutRequestHandler();
        });

        $requestHandlerLocator->register('get_current_version', $version = 1, function () {
            return $this->getMasterFactory()->createCurrentVersionApiV1GetRequestHandler();
        });

        $requestHandlerLocator->register('put_current_version', $version = 1, function () {
            return $this->getMasterFactory()->createCurrentVersionApiV1PutRequestHandler();
        });

        $requestHandlerLocator->register('get_templates', $version = 1, function () {
            return $this->getMasterFactory()->createTemplateApiV1GetRequestHandler();
        });
    }

    public function createCatalogImportApiV1PutRequestHandler(): CatalogImportApiV2PutRequestHandler
    {
        return new CatalogImportApiV1PutRequestHandler(
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->getLogger(),
            DataVersion::fromVersionString($this->getMasterFactory()->getCurrentDataVersion())
        );
    }

    private function getCatalogImportDirectoryConfig(): string
    {
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('catalog_import_directory');
    }

    public function createCatalogImportApiV2PutRequestHandler(): CatalogImportApiV2PutRequestHandler
    {
        return new CatalogImportApiV2PutRequestHandler(
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->getLogger()
        );
    }

    public function createContentBlocksApiV1PutRequestHandler(): ContentBlocksApiV1PutRequestHandler
    {
        return new ContentBlocksApiV1PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createContextBuilder(),
            $this->getMasterFactory()->createDataPoolReader()
        );
    }

    public function createContentBlocksApiV2PutRequestHandler(): ContentBlocksApiV2PutRequestHandler
    {
        return new ContentBlocksApiV2PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    public function createTemplatesApiV1PutRequestHandler(): TemplatesApiV1PutRequestHandler
    {
        return new TemplatesApiV1PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue(),
            DataVersion::fromVersionString($this->getMasterFactory()->getCurrentDataVersion())
        );
    }

    public function createTemplatesApiV2PutRequestHandler(): TemplatesApiV2PutRequestHandler
    {
        return new TemplatesApiV2PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue()
        );
    }

    public function createCurrentVersionApiV1GetRequestHandler(): CurrentVersionApiV1GetRequestHandler
    {
        return new CurrentVersionApiV1GetRequestHandler(
            $this->getMasterFactory()->createDataPoolReader()
        );
    }

    public function createCurrentVersionApiV1PutRequestHandler(): CurrentVersionApiV1PutRequestHandler
    {
        return new CurrentVersionApiV1PutRequestHandler(
            $this->getMasterFactory()->createCommandQueue()
        );
    }

    public function createProductImportApiV1PutRequestHandler()
    {
        return new ProductImportApiV1PutRequestHandler(
            $this->getMasterFactory()->createProductJsonToXml(),
            $this->getMasterFactory()->createCatalogImport()
        );
    }

    public function createProductJsonToXml(): ProductJsonToXml
    {
        return new ProductJsonToXml();
    }

    public function createTemplateApiV1GetRequestHandler()
    {
        return new TemplateApiV1GetRequestHandler($this->getMasterFactory()->createTemplateProjectorLocator());
    }
}
