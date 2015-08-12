<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductInSearchAutocompletionInContextSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_listing';

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ProductInSearchAutocompletionBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        SnippetList $snippetList,
        ProductInSearchAutocompletionBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->snippetList = $snippetList;
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    public function render(Product $product, Context $context)
    {
        $this->product = $product;
        $this->context = $context;
        $this->snippetList->clear();

        $this->addProductInSearchAutocompletionSnippetsToSnippetList();

        return $this->snippetList;
    }

    private function addProductInSearchAutocompletionSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->snippetKeyGenerator->getKeyForContext($this->context, ['product_id' => $this->product->getId()]);
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);
    }
}
