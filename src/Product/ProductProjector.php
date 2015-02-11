<?php

namespace Brera\Product;

use Brera\KeyValue\DataPoolWriter;
use Brera\Projector;
use Brera\ProjectionSourceData;
use Brera\Environment\EnvironmentSource;
use Brera\InvalidProjectionDataSourceType;

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
    public function __construct(ProductSnippetRendererCollection $rendererCollection, DataPoolWriter $dataPoolWriter)
    {
        $this->rendererCollection = $rendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param ProductSource|ProjectionSourceData $productSource
     * @param EnvironmentSource $environmentSource
     * @return void
     */
    public function project(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
        }
        $this->projectProduct($productSource, $environmentSource);
    }

    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     */
    private function projectProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
    {
        $snippetResultList = $this->rendererCollection->render($productSource, $environmentSource);
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);
    }
}
