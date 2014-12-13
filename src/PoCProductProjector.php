<?php

namespace Brera\PoC;

use Brera\PoC\Renderer\ProductRenderer;
use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Product\Product;

class PoCProductProjector
{
	/* TODO: Replace array with RendererCollection */
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
	    /* TODO: Looping is done inside of RendererCollection. Projector just calls render() on RendererCollection. */

	    foreach ($this->renderers as $renderer) {
		    // The projector renderer could be used even on the frontend.
		    // The renderer is decoupled from the data storage and display.

		    /* TODO: Make renderer return list of snippet outputs */

		    $html = $renderer->render($product);

		    /* TODO: Loop through returned results and put each to data pool */

		    $this->dataPoolWriter->setPoCProductHtml($product->getId(), $html);
	    }
    }
}

/**
 *
 * - Key generation goes from DataPoll writer to Snippet
 * - Then projector injects the list of snippets into DataPoolWriter
 * - And DataPool writer gets key and content from each snippet and puts it into key/value storage
 *
 */
