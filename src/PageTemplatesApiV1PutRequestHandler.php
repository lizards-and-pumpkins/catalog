<?php

namespace Brera;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

class PageTemplatesApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var RootSnippetSourceListBuilder
     */
    private $rootSnippetSourceListBuilder;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(RootSnippetSourceListBuilder $rootSnippetSourceListBuilder, Queue $domainEventQueue)
    {
        $this->rootSnippetSourceListBuilder = $rootSnippetSourceListBuilder;
        $this->domainEventQueue = $domainEventQueue;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        if (HttpRequest::METHOD_PUT !== $request->getMethod()) {
            return false;
        }

        if (null === $this->extractRootSnippetIdFromUrl($request)) {
            return false;
        }

        return true;
    }

    protected function processRequest(HttpRequest $request)
    {
        $rootSnippetId = $this->extractRootSnippetIdFromUrl($request);
        $rootSnippetSourceList = $this->rootSnippetSourceListBuilder->fromJson($request->getRawBody());
        $this->domainEventQueue->add(new PageTemplateWasUpdatedDomainEvent($rootSnippetId, $rootSnippetSourceList));
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
    {
        return json_encode('OK');
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractRootSnippetIdFromUrl(HttpRequest $request)
    {
        preg_match('#/page_templates/([^/]+)#i', $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
