<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;

class TemplateApiV1GetRequestHandler implements HttpRequestHandler
{
    /**
     * @var TemplateProjectorLocator
     */
    private $templateProjectorLocator;

    public function __construct(TemplateProjectorLocator $templateProjectorLocator)
    {
        $this->templateProjectorLocator = $templateProjectorLocator;
    }

    public function canProcess(HttpRequest $request): bool
    {
        return $request->getMethod() === HttpRequest::METHOD_GET;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        return GenericHttpResponse::create(
            $body = json_encode(['template_ids' => $this->templateProjectorLocator->getRegisteredProjectorCodes()]),
            $headers = [],
            HttpResponse::STATUS_OK
        );
    }
}
