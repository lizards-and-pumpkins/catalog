<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;
use Brera\SnippetResult;
use Brera\SnippetResultList;
use Brera\ThemeLocator;

class ProductDetailViewBlockRenderer extends BlockRenderer
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ThemeLocator $themeLocator
     * @param BlockStructure $blockStructure
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ThemeLocator $themeLocator,
        BlockStructure $blockStructure
    ) {
        parent::__construct($themeLocator, $blockStructure);
        $this->snippetResultList = $snippetResultList;
    }
    
    /**
     * @param Product $product
     * @param Context $context
     * @return SnippetResultList
     */
    public function getFilledSnippetResultList(Product $product, Context $context)
    {
        $this->product = $product;
        $this->context = $context;
        $this->snippetResultList->clear();
        
        $contentSnippet = $this->getContentSnippet();
        $childSnippetListSnippet = $this->getChildSnippetListSnippet($contentSnippet);
        $urlSnippet = $this->getProductUrlKeySnippet($contentSnippet);

        $this->snippetResultList->add($contentSnippet);
        $this->snippetResultList->add($childSnippetListSnippet);
        $this->snippetResultList->add($urlSnippet);
        
        return $this->snippetResultList;
    }

    /**
     * @return SnippetResult
     */
    private function getContentSnippet()
    {
        $snippetContent = $this->render($this->product, $this->context);
        $snippetKey = $this->getContentSnippetKey();
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param SnippetResult $targetSnippet
     * @return SnippetResult
     */
    private function getProductUrlKeySnippet(SnippetResult $targetSnippet)
    {
        $snippetKey = $this->getUrlSnippetKey($this->product->getAttributeValue('url_key'));
        $snippetContent = $targetSnippet->getKey();
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param SnippetResult $targetSnippet
     * @return SnippetResult
     */
    private function getChildSnippetListSnippet(SnippetResult $targetSnippet)
    {
        $snippetKey = $this->getUrlPathKeyGenerator()->getChildSnippetListKey($targetSnippet->getKey());
        $snippetContent = json_encode([$snippetKey]);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @return string
     */
    private function getContentSnippetKey()
    {
        return $this->getSnippetKeyGenerator()->getKeyForContext($this->product->getId(), $this->context);
    }

    /**
     * @param string $urlKey
     * @return string
     */
    private function getUrlSnippetKey($urlKey)
    {
        return $this->getUrlPathKeyGenerator()->getUrlKeyForPathInContext($urlKey, $this->context);
    }

    /**
     * @return string
     */
    protected function getLayoutHandle()
    {
        return 'product_detail_view';
    }
}
