<?php

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\SameSeriesProductRelations;
use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\RegistersDelegateFactory;

class ProductRelationsFactory implements Factory, RegistersDelegateFactory
{
    use FactoryTrait;

    /**
     * @return ProductRelationsService
     */
    public function createProductRelationsService()
    {
        return new ProductRelationsService(
            $this->getMasterFactory()->createProductRelationsLocator(),
            $this->getMasterFactory()->createProductJsonService(),
            $this->getMasterFactory()->createContext()
        );
    }

    /**
     * @return SameSeriesProductRelations
     */
    public function createSameSeriesProductRelations()
    {
        return new SameSeriesProductRelations(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createContext()
        );
    }

    /**
     * @return ProductRelationsLocator
     */
    public function createProductRelationsLocator()
    {
        $productRelationsLocator = new ProductRelationsLocator();
        $productRelationsLocator->register(
            ProductRelationTypeCode::fromString('related-models'),
            [$this->getMasterFactory(), 'createSameSeriesProductRelations']
        );
        return $productRelationsLocator;
    }

    /**
     * @return ProductRelationsApiV1GetRequestHandler
     */
    public function createProductRelationsApiV1GetRequestHandler()
    {
        return new ProductRelationsApiV1GetRequestHandler(
            $this->getMasterFactory()->createProductRelationsService()
        );
    }

    public function registerDelegateFactories(MasterFactory $masterFactory)
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
