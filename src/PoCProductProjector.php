<?php


namespace Brera\PoC;


class PoCProductProjector
{
    /**
     * @var ProductRenderer
     */
    private $renderer;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    public function __construct(
        ProductRenderer $renderer,
        DataPoolWriter $dataPoolWriter
    )
    {
        $this->renderer = $renderer;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param Product $product
     */
    public function project(Product $product)
    {
        // The projector renderer could be used even on the frontend.
        // The renderer is decoupled from the data storage and display.
        $html = $this->renderer->render($product);
        $this->dataPoolWriter->setPoCProductHtml($product->getId(), $html);
    }
}
