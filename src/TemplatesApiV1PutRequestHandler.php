<?php

namespace Brera;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

class TemplatesApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(Queue $domainEventQueue)
    {
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

        if (null === $this->extractRootSnippetIdFromRequest($request)) {
            return false;
        }

        return true;
    }

    protected function processRequest(HttpRequest $request)
    {
        $rootSnippetId = $this->extractRootSnippetIdFromRequest($request);
        $this->domainEventQueue->add(new TemplateWasUpdatedDomainEvent($rootSnippetId, $request->getRawBody()));
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
    private function extractRootSnippetIdFromRequest(HttpRequest $request)
    {
        preg_match('#/templates/([^/]+)#i', $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
