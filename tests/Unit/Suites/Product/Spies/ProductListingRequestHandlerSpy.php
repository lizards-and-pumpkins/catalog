<?php


namespace Brera\Product\Spies;

use Brera\Product\ProductListingRequestHandler;

class ProductListingRequestHandlerSpy extends ProductListingRequestHandler
{
    /**
     * @param string $json
     * @return \Brera\Product\ProductListingMetaInfoSnippetContent
     */
    public function testCreatePageMetaInfoInstance($json)
    {
        return $this->createPageMetaInfoInstance($json);
    }
    
    public function testAddPageSpecificAdditionalSnippetsHook()
    {
        $this->addPageSpecificAdditionalSnippetsHook();
    }

    /**
     * @return string
     */
    public function testGetPageMetaInfoSnippetKey()
    {
        return $this->getPageMetaInfoSnippetKey();
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
