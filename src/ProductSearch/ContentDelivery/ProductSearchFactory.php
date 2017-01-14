<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductSearchFactory implements Factory, FactoryWithCallback
{
    use FactoryTrait;

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        $apiVersion = 1;

        /** @var ApiRequestHandlerLocator $apiRequestHandlerLocator */
        $apiRequestHandlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $apiRequestHandlerLocator->register(
            'get_product',
            $apiVersion,
            $this->getMasterFactory()->createProductSearchApiV1GetRequestHandler()
        );
    }

    public function createProductSearchApiV1GetRequestHandler() : ProductSearchApiV1GetRequestHandler
    {
        return new ProductSearchApiV1GetRequestHandler(
            $this->getMasterFactory()->createProductSearchService(),
            $this->getMasterFactory()->createContextBuilder(),
            $this->getMasterFactory()->getDefaultNumberOfProductsPerSearchResultsPage(),
            $this->getMasterFactory()->getMaxAllowedProductsPerSearchResultsPage(),
            $this->getMasterFactory()->getProductSearchDefaultSortBy(),
            ...$this->getMasterFactory()->getSortableAttributeCodes()
        );
    }

    public function createProductSearchService() : ProductSearchService
    {
        return new ProductSearchService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createGlobalProductListingCriteria(),
            $this->getMasterFactory()->createProductJsonService()
        );
    }
}
