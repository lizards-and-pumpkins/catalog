<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;

class TemplatesApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(CommandQueue $commandQueue, DataVersion $dataVersion)
    {
        $this->commandQueue = $commandQueue;
        $this->dataVersion = $dataVersion;
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
        $this->commandQueue->add(new UpdateTemplateCommand($templateId, $request->getRawBody(), $this->dataVersion));
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
