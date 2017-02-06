<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
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

class ContentBlocksApiV2PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(
        CommandQueue $commandQueue,
        ContextBuilder $contextBuilder,
        DataPoolReader $dataPoolReader
    ) {
        $this->commandQueue = $commandQueue;
        $this->contextBuilder = $contextBuilder;
        $this->dataPoolReader = $dataPoolReader;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        if (HttpRequest::METHOD_PUT !== $request->getMethod()) {
            return false;
        }

        if (null === $this->extractContentBlockIdFromUrl($request)) {
            return false;
        }

        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
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
        
        $context = $this->contextBuilder->createContext(array_merge(
            $requestBody['context'],
            [DataVersion::CONTEXT_CODE => $this->getDataVersion($requestBody)]
        ));
        $contentBlockSource = new ContentBlockSource(
            $contentBlockId,
            $requestBody['content'],
            $context,
            $keyGeneratorParams
        );

        $this->commandQueue->add(new UpdateContentBlockCommand($contentBlockSource));
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
        preg_match('#/content_blocks/([^/]+)#i', (string) $request->getUrl(), $urlTokens);

        if (count($urlTokens) < 2) {
            return null;
        }

        return $urlTokens[1];
    }

    /**
     * @param string[] $requestBody
     * @return string
     */
    protected function getDataVersion(array $requestBody): string
    {
        return $this->dataPoolReader->getCurrentDataVersion();
    }
}
