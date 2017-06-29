<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration;
use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductSearchApiFactory implements FactoryWithCallback
{
    use FactoryWithCallbackTrait;

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        $masterFactory->register(new ProductSearchSharedFactory());
        $this->registerProductSearchApiEndpoint($masterFactory);
    }

    public function createProductSearchApiV1GetRequestHandler(): ProductSearchApiV1GetRequestHandler
    {
        return new ProductSearchApiV1GetRequestHandler(
            $this->getMasterFactory()->createProductSearchService(),
            $this->getMasterFactory()->createContextBuilder(),
            $this->getMasterFactory()->createUrlToWebsiteMap(),
            $this->getMasterFactory()->createFullTextCriteriaBuilder(),
            $this->getMasterFactory()->createSelectedFiltersParser(),
            $this->getMasterFactory()->createCriteriaParser(),
            $this->getMasterFactory()->createDefaultSearchEngineConfiguration()
        );
    }

    public function createSelectedFiltersParser(): SelectedFiltersParser
    {
        return new DefaultSelectedFiltersParser();
    }

    public function createCriteriaParser(): CriteriaParser
    {
        return new DefaultCriteriaParser();
    }

    public function createDefaultSearchEngineConfiguration(): SearchEngineConfiguration
    {
        return new SearchEngineConfiguration(
            $this->getMasterFactory()->getDefaultNumberOfProductsPerSearchResultsPage(),
            $this->getMasterFactory()->getMaxAllowedProductsPerSearchResultsPage(),
            $this->getMasterFactory()->getProductSearchDefaultSortBy(),
            ...$this->getMasterFactory()->getSortableAttributeCodes()
        );
    }

    private function registerProductSearchApiEndpoint(MasterFactory $masterFactory)
    {
        $apiVersion = 1;

        /** @var ApiRequestHandlerLocator $apiRequestHandlerLocator */
        $apiRequestHandlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $apiRequestHandlerLocator->register('get_product', $apiVersion, function () {
            return $this->getMasterFactory()->createProductSearchApiV1GetRequestHandler();
        });
    }
}
