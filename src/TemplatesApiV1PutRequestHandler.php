<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Queue\Queue;

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

        if (null === $this->extractTemplateIdFromRequest($request)) {
            return false;
        }

        return true;
    }

    protected function processRequest(HttpRequest $request)
    {
        $templateId = $this->extractTemplateIdFromRequest($request);
        // todo: add command which validates input data to command queue, the have the command handler create the event
        $this->domainEventQueue->add(new TemplateWasUpdatedDomainEvent($templateId, $request->getRawBody()));
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
    private function extractTemplateIdFromRequest(HttpRequest $request)
    {
        preg_match('#/templates/([^/]+)#i', $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
