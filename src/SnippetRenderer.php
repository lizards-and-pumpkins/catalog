<?php
namespace Brera\PoC;

interface SnippetRenderer
{
    /**
     * @todo think about typehint, idea at the moment: empty InputData marker interface
     *
     * @param             $product
     * @param Environment $environment
     *
     * @return SnippetResultList
     */
    public function render($product, Environment $environment);
}
