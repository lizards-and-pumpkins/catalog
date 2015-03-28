<?php

namespace Brera\Product;

use Brera\AbstractHttpRequestHandler;
use Brera\PageMetaInfoSnippetContent;

class ProductListingRequestHandler extends AbstractHttpRequestHandler
{
    /**
     * @var string
     */
    private $listingTypeId;

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
     * @param string $key
     * @return string
     */
    protected function getSnippetKeyInContext($key)
    {
        $keyGenerator = $this->getKeyGeneratorLocator()->getKeyGeneratorForSnippetCode($key);
        return $keyGenerator->getKeyForContext($this->listingTypeId, $this->getContext());
    }

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    protected function createPageMetaInfoInstance($snippetJson)
    {
        $metaInfo = PageMetaInfoSnippetContent::fromJson($snippetJson);
        $this->listingTypeId = $metaInfo->getSourceId();
        return $metaInfo;
    }
}
