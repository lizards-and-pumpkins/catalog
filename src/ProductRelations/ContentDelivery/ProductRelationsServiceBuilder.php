<?php

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductRelationsServiceBuilder
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }

    public function getForContext(Context $context) : ProductRelationsService
    {
        return $this->masterFactory->createProductRelationsService($context);
    }
}
