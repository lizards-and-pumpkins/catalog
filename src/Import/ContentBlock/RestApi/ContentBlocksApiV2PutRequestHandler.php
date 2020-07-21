<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\MissingContentBlockDataVersionException;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockBodyIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockContextIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockContextException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockUrlKey;
use LizardsAndPumpkins\Http\HttpRequest;

class ContentBlocksApiV2PutRequestHandler implements HttpRequestHandler
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(CommandQueue $commandQueue, ContextBuilder $contextBuilder)
    {
        $this->commandQueue = $commandQueue;
        $this->contextBuilder = $contextBuilder;
    }

    public function canProcess(HttpRequest $request): bool
    {
        if (HttpRequest::METHOD_PUT !== $request->getMethod()) {
            return false;
        }

        if (null === $this->extractContentBlockIdFromUrl($request)) {
            return false;
        }

        return true;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        $requestBody = json_decode($request->getRawBody(), true);
        $this->validateRequestBody($requestBody);

        $contentBlockSource = new ContentBlockSource(
            $this->getContentBlockIdFromRequest($request),
            $requestBody['content'],
            $this->buildContextFromRequest($requestBody),
            $this->buildKeyGeneratorParamsFromRequest($requestBody)
        );

        $this->commandQueue->add(new UpdateContentBlockCommand($contentBlockSource));

        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_ACCEPTED);
    }

    /**
     * @param string[] $requestBody
     */
    private function validateRequestBody(array $requestBody)
    {
        $this->validateContent($requestBody);
        $this->validateContext($requestBody);
        $this->validateUrlKey($requestBody);
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractContentBlockIdFromUrl(HttpRequest $request)
    {
        if (! preg_match('#/content_blocks/(?<content_block_id>[^/]+)#i', (string) $request->getUrl(), $urlTokens)) {
            return null;
        }

        return $urlTokens['content_block_id'];
    }

    /**
     * @param string[] $requestBody
     * @return string
     */
    protected function getDataVersion(array $requestBody): string
    {
        $this->validateDataVersion($requestBody);
        return $requestBody['data_version'];
    }

    private function validateDataVersion(array $requestBody)
    {
        if (!isset($requestBody['data_version'])) {
            throw new MissingContentBlockDataVersionException('The content block data version must be specified.');
        }

        DataVersion::fromVersionString($requestBody['data_version']);
    }

    private function validateContext(array $requestBody)
    {
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
    }

    private function validateContent(array $requestBody)
    {
        if (!isset($requestBody['content'])) {
            throw new ContentBlockBodyIsMissingInRequestBodyException(
                'Content block content is missing in request body.'
            );
        }
    }

    private function validateUrlKey(array $requestBody)
    {
        if (isset($requestBody['url_key']) && !is_string($requestBody['url_key'])) {
            throw new InvalidContentBlockUrlKey(
                sprintf('Content block URL key must be a string, got %s.', gettype($requestBody['url_key']))
            );
        }
    }

    protected function getContentBlockIdFromRequest(HttpRequest $request): ContentBlockId
    {
        $contentBlockIdString = $this->extractContentBlockIdFromUrl($request);

        return ContentBlockId::fromString($contentBlockIdString);
    }

    private function buildKeyGeneratorParamsFromRequest($requestBody): array
    {
        return isset($requestBody['url_key']) ?
            ['url_key' => $requestBody['url_key']] :
            [];
    }

    private function buildContextFromRequest($requestBody): Context
    {
        return $this->contextBuilder->createContext(array_merge(
            $requestBody['context'],
            [DataVersion::CONTEXT_CODE => $this->getDataVersion($requestBody)]
        ));
    }
}
