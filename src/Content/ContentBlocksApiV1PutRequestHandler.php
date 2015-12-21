<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Content\Exception\ContentBlockBodyIsMissingInRequestBodyException;
use LizardsAndPumpkins\Content\Exception\ContentBlockContextIsMissingInRequestBodyException;
use LizardsAndPumpkins\Content\Exception\InvalidContentBlockUrlKey;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Queue\Queue;

class ContentBlocksApiV1PutRequestHandler extends ApiRequestHandler
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
        return json_encode('OK');
    }

    protected function processRequest(HttpRequest $request)
    {
        $requestBody = json_decode($request->getRawBody(), true);
        $this->validateRequestBody($requestBody);

        $contentBlockIdString = $this->extractContentBlockIdFromUrl($request);
        $contentBlockId = ContentBlockId::fromString($contentBlockIdString);

        $keyGeneratorParams = [];

        if (isset($requestBody['url_key'])) {
            $keyGeneratorParams['url_key'] = $requestBody['url_key'];
        }

        $contentBlockSource = new ContentBlockSource(
            $contentBlockId,
            $requestBody['content'],
            $requestBody['context'],
            $keyGeneratorParams
        );

        $this->commandQueue->add(new UpdateContentBlockCommand($contentBlockSource));
    }

    /**
     * @param string[] $requestBody
     */
    protected function validateRequestBody(array $requestBody)
    {
        if (!isset($requestBody['content'])) {
            throw new ContentBlockBodyIsMissingInRequestBodyException(
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

        if (isset($requestBody['url_key']) && !is_string($requestBody['url_key'])) {
            throw new InvalidContentBlockUrlKey(
                sprintf('Content block URL key must be a string, got %s.', gettype($requestBody['url_key']))
            );
        }
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractContentBlockIdFromUrl(HttpRequest $request)
    {
        preg_match('#/content_blocks/([^/]+)#i', $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }
}
