<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory;
use LizardsAndPumpkins\RestApi\CatalogRestApiFactory;
use LizardsAndPumpkins\RestApi\RestApiWebFront;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;

class CatalogRestApiWebFront extends RestApiWebFront
{
    protected function createMasterFactory(): MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory): void
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new CatalogRestApiFactory());
        $masterFactory->register(new ContentBlockServiceFactory());
        $masterFactory->register(new ProductSearchApiFactory());
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());

        parent::registerFactories($masterFactory);
    }
}
