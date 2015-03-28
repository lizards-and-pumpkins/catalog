<?php

namespace Brera\Product;

use Brera\AbstractHttpRequestHandler;
use Brera\PageMetaInfoSnippetContent;

class ProductDetailViewRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @return string
     */
    final protected function getPageMetaInfoSnippetKey()
    {
        return $this->getUrlPathKeyGenerator()->getUrlKeyForUrlInContext(
            $this->getHttpUrl(),
            $this->getContext()
        );
    }

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = PageMetaInfoSnippetContent::fromJson($snippetJson);
        $this->productId = $metaInfo->getSourceId();
        return $metaInfo;
    }

    /**
     * @param string $key
     * @return string
     */
    final protected function getSnippetKeyInContext($key)
    {
        $keyGenerator = $this->getKeyGeneratorLocator()->getKeyGeneratorForSnippetCode($key);
        return $keyGenerator->getKeyForContext($this->productId, $this->getContext());
    }
}
