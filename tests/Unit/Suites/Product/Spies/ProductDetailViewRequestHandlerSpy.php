<?php


namespace Brera\Product\Spies;

use Brera\Product\ProductDetailPageMetaInfoSnippetContent;
use Brera\Product\ProductDetailViewRequestHandler;

class ProductDetailViewRequestHandlerSpy extends ProductDetailViewRequestHandler
{
    /**
     * @param string $json
     * @return ProductDetailPageMetaInfoSnippetContent
     */
    public function testCreatePageMetaInfoInstance($json)
    {
        return $this->createPageMetaInfoInstance($json);
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    public function testGetSnippetKey($snippetCode)
    {
        return $this->getSnippetKey($snippetCode);
    }

    /**
     * @return string
     */
    public function testGetPageMetaInfoSnippetKey()
    {
        return $this->getPageMetaInfoSnippetKey();
    }

    /**
     * @param string $snippetKey
     * @return string
     */
    public function testFormatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return $this->formatSnippetNotAvailableErrorMessage($snippetKey);
    }

    /**
     * @return \Brera\DataPool\DataPoolReader
     */
    public function testGetDataPoolReader()
    {
        return $this->getDataPoolReader();
    }

    /**
     * @return \Brera\Logger
     */
    public function testGetLogger()
    {
        return $this->getLogger();
    }
}
