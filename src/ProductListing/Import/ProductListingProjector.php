<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;

class ProductListingProjector implements Projector
{
    /**
     * @var Projector
     */
    private $snippetProjector;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    /**
     * @var UrlKeyForContextCollector
     */
    private $urlKeyForContextCollector;

    public function __construct(
        Projector $snippetProjector,
        UrlKeyForContextCollector $urlKeyForContextCollector,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->snippetProjector = $snippetProjector;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->urlKeyForContextCollector = $urlKeyForContextCollector;
    }

    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        $this->projectSnippets($projectionSourceData);

        $urlKeysForContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys($projectionSourceData);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }

    private function projectSnippets(ProductListing $productListing)
    {
        $this->snippetProjector->project($productListing);
    }
}
