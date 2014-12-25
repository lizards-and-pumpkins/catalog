<?php

namespace Brera\PoC\Product;

use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Projector;
use Brera\PoC\ProjectionSourceData;
use Brera\PoC\Environment;
use Brera\PoC\InvalidProjectionDataSourceType;

class ProductProjector implements Projector
{
    /**
     * @var ProductSnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @param ProductSnippetRendererCollection $rendererCollection
     * @param DataPoolWriter $dataPoolWriter
     */
    public function __construct(
        ProductSnippetRendererCollection $rendererCollection,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->rendererCollection = $rendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param Product|ProjectionSourceData $product
     * @param Environment $environment
     * @throws InvalidProjectionDataSourceType
     */
    public function project(ProjectionSourceData $product, Environment $environment)
    {
        if (!($product instanceof Product)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
        }
        $this->projectProduct($product, $environment);
    }

    /**
     * @param Product $product
     * @param Environment $environment
     */
    private function projectProduct(Product $product, Environment $environment)
    {
        $snippetResultList = $this->rendererCollection->render(
            $product, $environment
        );
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);
    }
}
