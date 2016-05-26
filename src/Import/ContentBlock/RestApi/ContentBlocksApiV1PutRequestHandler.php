<?php

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockBodyIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockContextIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockContextException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockUrlKey;
use LizardsAndPumpkins\Http\HttpRequest;

class ContentBlocksApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    public function __construct(CommandQueue $commandQueue)
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
     * @return HttpResponse
     */
    final protected function getResponse(HttpRequest $request)
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }

    final protected function processRequest(HttpRequest $request)
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

        $this->commandQueue->add('update_content_block', $contentBlockSource->serialize());
    }

    /**
     * @param string[] $requestBody
     */
    private function validateRequestBody(array $requestBody)
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
            throw new InvalidContentBlockContextException(
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
