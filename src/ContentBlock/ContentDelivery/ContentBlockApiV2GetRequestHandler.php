<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\UnableToProcessContentBlockApiGetRequestException;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

class ContentBlockApiV2GetRequestHandler implements HttpRequestHandler
{
    const ENDPOINT = 'content_blocks';

    /**
     * @var ContentBlockService
     */
    private $contentBlockService;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(ContentBlockService $contentBlockService, ContextBuilder $contextBuilder)
    {
        $this->contentBlockService = $contentBlockService;
        $this->contextBuilder = $contextBuilder;
    }

    public function canProcess(HttpRequest $request): bool
    {
        return '' !== $this->getBlockIdFromRequest($request);
    }

    public function process(HttpRequest $request): HttpResponse
    {
        if (! $this->canProcess($request)) {
            throw new UnableToProcessContentBlockApiGetRequestException('canProcess must be called before process');
        }

        $contentBlockId = $this->getBlockIdFromRequest($request);
        $context = $this->contextBuilder->createFromRequest($request);

        try {
            $responseBody = $this->contentBlockService->getContentBlock($contentBlockId, $context);
            $statusCode = HttpResponse::STATUS_OK;
        } catch (ContentBlockNotFoundException $e) {
            $responseBody = sprintf('Content block "%s" does not exist.', $contentBlockId);
            $statusCode = HttpResponse::STATUS_NOT_FOUND;
        }

        return GenericHttpResponse::create($responseBody, [], $statusCode);
    }

    private function getBlockIdFromRequest(HttpRequest $request): string
    {
        $encodedBlockId = preg_replace('/.*\/' . self::ENDPOINT . '\/|\?.*|#.*/', '', $request->getUrl());

        return trim(urldecode($encodedBlockId), "/ \t\n\r\0\x0B");
    }
}
