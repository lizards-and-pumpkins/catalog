<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;

class TemplatesApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(DomainEventQueue $domainEventQueue)
    {
        $this->domainEventQueue = $domainEventQueue;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        if (HttpRequest::METHOD_PUT !== $request->getMethod()) {
            return false;
        }

        if (null === $this->extractTemplateIdFromRequest($request)) {
            return false;
        }

        return true;
    }

    final protected function processRequest(HttpRequest $request)
    {
        $templateId = $this->extractTemplateIdFromRequest($request);
        // todo: add command which validates input data to command queue, the have the command handler create the event
        $this->domainEventQueue->add(new TemplateWasUpdatedDomainEvent($templateId, $request->getRawBody()));
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractTemplateIdFromRequest(HttpRequest $request)
    {
        preg_match('#/templates/([^/]+)#i', (string) $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
