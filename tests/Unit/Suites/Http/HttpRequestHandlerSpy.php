<?php

namespace Brera\Http;

use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\PageMetaInfoSnippetContent;

class HttpRequestHandlerSpy extends AbstractHttpRequestHandler
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Logger
     */
    private $logger;

    public $hookWasCalled = false;

    /**
     * @var PageMetaInfoSnippetContent
     */
    private $pageMetaInfoStub;
    /**
     * @var
     */
    private $pageMetaInfoKey;

    /**
     * @var \Exception
     */
    private $snippetKeyLookupException;

    /**
     * @param DataPoolReader $dataPoolReader
     * @param Logger $logger
     * @param PageMetaInfoSnippetContent $pageMetaInfo
     * @param string $pageMetaInfoKey
     */
    public function __construct(
        DataPoolReader $dataPoolReader,
        Logger $logger,
        PageMetaInfoSnippetContent $pageMetaInfo,
        $pageMetaInfoKey
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->logger = $logger;
        $this->pageMetaInfoStub = $pageMetaInfo;
        $this->pageMetaInfoKey = $pageMetaInfoKey;
    }

    protected function addPageSpecificAdditionalSnippetsHook()
    {
        $this->hookWasCalled = true;
    }

    /**
     * @param string[] $snippetCodeToKeyMap
     * @param string[] $snippetKeyToContentMap
     */
    public function testAddSnippetsToPage(array $snippetCodeToKeyMap, array $snippetKeyToContentMap)
    {
        $this->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    public function setThrowExceptionDuringSnippetKeyLookup(\Exception $exception)
    {
        $this->snippetKeyLookupException = $exception;
    }

    /**
     * @return string
     */
    protected function getPageMetaInfoSnippetKey()
    {
        return $this->pageMetaInfoKey;
    }

    /**
     * @param string $snippetJson
     * @return PageMetaInfoSnippetContent
     */
    protected function createPageMetaInfoInstance($snippetJson)
    {
        return $this->pageMetaInfoStub;
    }

    /**
     * @param string $snippetCode
     * @return string
     * @throws \Exception
     */
    protected function getSnippetKey($snippetCode)
    {
        if ($this->snippetKeyLookupException) {
            throw $this->snippetKeyLookupException;
        }
        return (string)$snippetCode;
    }

    /**
     * @param string $snippetKey
     * @return string string
     */
    protected function formatSnippetNotAvailableErrorMessage($snippetKey)
    {
        return sprintf('Snippet "%s" not available', $snippetKey);
    }

    /**
     * @return DataPoolReader
     */
    protected function getDataPoolReader()
    {
        return $this->dataPoolReader;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }
}
