<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class EnrichProductJsonWithPricesBuilder
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }

    public function getForContext(Context $context) : EnrichProductJsonWithPrices
    {
        return $this->masterFactory->createEnrichProductJsonWithPrices($context);
    }
}
