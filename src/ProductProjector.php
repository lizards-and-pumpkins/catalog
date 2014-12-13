<?php

namespace Brera\PoC;

use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Product\Product;

class ProductProjector
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
     * @param DataPoolWriter                   $dataPoolWriter
     */
    public function __construct(
        ProductSnippetRendererCollection $rendererCollection,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->rendererCollection = $rendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param Product $product
     */
    public function project(Product $product, Environment $environment)
    {
        $snippetResultList = $this->rendererCollection->render(
            $product, $environment
        );
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);
    }

    public function merge(){
        
    }
}
