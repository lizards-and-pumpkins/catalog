<?php

namespace Brera\Content;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

class ContentBlocksApiRequestHandler extends ApiRequestHandler
{
    /**
     * @var Queue
     */
    private $commandQueue;

    public function __construct(Queue $commandQueue)
    {
        $this->commandQueue = $commandQueue;
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

        if (null === $this->extractContentBlockIdFromUrl($request)) {
            return false;
        }

        return true;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
    {
        $requestBody = json_decode($request->getRawBody(), true);
        $this->validateRequestBody($requestBody);

        $contentBlockIdString = $this->extractContentBlockIdFromUrl($request);
        $contentBlockId = ContentBlockId::fromString($contentBlockIdString);
        $contentBlockSource = new ContentBlockSource($contentBlockId, $requestBody['content'], $requestBody['context']);

        $this->commandQueue->add(new UpdateContentBlockCommand($contentBlockId, $contentBlockSource));

        return json_encode('OK');
    }

    /**
     * @param string $requestBody
     */
    protected function validateRequestBody($requestBody)
    {
        if (!isset($requestBody['content'])) {
            throw new ContentBlockContentIsMissingInRequestBodyException(
                'Content block content is missing in request body.'
            );
        }

        if (!isset($requestBody['context'])) {
            throw new ContentBlockContextIsMissingInRequestBodyException(
                'Content block context is missing in request body.'
            );
        }

        if (!is_array($requestBody['context'])) {
            throw new InvalidContentBlockContext(
                sprintf('Content block context supposed to be an array, got %s.', gettype($requestBody['context']))
            );
        }
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractContentBlockIdFromUrl(HttpRequest $request)
    {
        $urlStartingFromEndpoint = stristr($request->getUrl(), '/content_blocks/');
        $trimmedUrlStartingFromEndpoint = trim($urlStartingFromEndpoint, '/');
        $urlTokens = explode('/', $trimmedUrlStartingFromEndpoint);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
