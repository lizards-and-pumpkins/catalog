<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsServiceBuilder;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\SameSeriesProductRelations;
use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductRelationsFactory implements Factory, FactoryWithCallback
{
    use FactoryTrait;

    public function createProductRelationsService(Context $context) : ProductRelationsService
    {
        return new ProductRelationsService(
            $this->getMasterFactory()->createProductRelationsLocator(),
            $this->getMasterFactory()->createProductJsonService($context),
            $context
        );
    }

    public function createProductRelationsServiceBuilder() : ProductRelationsServiceBuilder
    {
        return new ProductRelationsServiceBuilder(
            $this->getMasterFactory()
        );
    }

    public function createSameSeriesProductRelations() : SameSeriesProductRelations
    {
        return new SameSeriesProductRelations(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createContext()
        );
    }

    public function createProductRelationsLocator() : ProductRelationsLocator
    {
        $productRelationsLocator = new ProductRelationsLocator();
        $productRelationsLocator->register(
            ProductRelationTypeCode::fromString('related-models'),
            [$this->getMasterFactory(), 'createSameSeriesProductRelations']
        );
        return $productRelationsLocator;
    }

    public function createProductRelationsApiV1GetRequestHandler() : ProductRelationsApiV1GetRequestHandler
    {
        return new ProductRelationsApiV1GetRequestHandler(
            $this->getMasterFactory()->createProductRelationsServiceBuilder(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        $apiVersion = 1;

        /** @var ApiRequestHandlerLocator $apiRequestHandlerLocator */
        $apiRequestHandlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $apiRequestHandlerLocator->register(
            'get_products',
            $apiVersion,
            $this->getMasterFactory()->createProductRelationsApiV1GetRequestHandler()
        );
    }
}
