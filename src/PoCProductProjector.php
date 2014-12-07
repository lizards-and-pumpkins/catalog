<?php

namespace Brera\PoC;

use Brera\PoC\Renderer\ProductRenderer;
use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Product\Product;

class PoCProductProjector
{
    /**
     * @var ProductRenderer[]
     */
    private $renderers;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @param ProductRenderer[] $renderers
     * @param DataPoolWriter $dataPoolWriter
     */
    public function __construct($renderers, DataPoolWriter $dataPoolWriter)
    {
        $this->renderers = $renderers;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param Product $product
     */
    public function project(Product $product)
    {
	    foreach ($this->renderers as $renderer) {
		    // The projector renderer could be used even on the frontend.
		    // The renderer is decoupled from the data storage and display.
		    $html = $renderer->render($product);
		    $this->dataPoolWriter->setPoCProductHtml($product->getId(), $html);
	    }
    }
}
