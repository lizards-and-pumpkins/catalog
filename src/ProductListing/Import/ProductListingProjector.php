<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
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
     * @param ProductListing $productListing
     */
    public function project($productListing): void
    {
        if (! $productListing instanceof ProductListing) {
            throw new InvalidProjectionSourceDataTypeException(
                sprintf('Projection source data must be of ProductListing type, got "%s".', typeof($productListing))
            );
        }

        $this->snippetProjector->project($productListing);

        $urlKeysForContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys($productListing);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
