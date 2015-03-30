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

    public function __construct(DataPoolReader $dataPoolReader, Logger $logger, PageMetaInfoSnippetContent $meta)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->logger = $logger;
        $this->pageMetaInfoStub = $meta;
    }

    /**
     * @return \string[]
     */
    protected function getPageSpecificAdditionalSnippetsHook()
    {
        $this->hookWasCalled = true;
        return ['dummy' => 'content'];
    }
    
    /**
     * @return string
     */
    protected function getPageMetaInfoSnippetKey()
    {
        return 'dummy';
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
     */
    protected function getSnippetKey($snippetCode)
    {
        return (string) $snippetCode;
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
