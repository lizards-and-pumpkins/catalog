<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\Exception\InvalidTemplateApiRequestBodyException;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;

class TemplatesApiV2PutRequestHandler implements HttpRequestHandler
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    public function __construct(CommandQueue $commandQueue)
    {
        $this->commandQueue = $commandQueue;
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

    final public function process(HttpRequest $request): HttpResponse
    {
        $templateId = $this->extractTemplateIdFromRequest($request);
        $templateContent = $this->getContent($request);
        $dataVersion = $this->getDataVersion($request);
        $this->commandQueue->add(new UpdateTemplateCommand($templateId, $templateContent, $dataVersion));

        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_ACCEPTED);
    }

    protected function getContent(HttpRequest $request): string
    {
        return $this->extractContentFromRequest($request);
    }

    protected function getDataVersion(HttpRequest $request): DataVersion
    {
        return DataVersion::fromVersionString($this->extractDataVersionFromRequest($request));
    }

    /**
     * @param HttpRequest $request
     * @return string|null
     */
    private function extractTemplateIdFromRequest(HttpRequest $request)
    {
        if (! preg_match('#/templates/(?<template_id>[^/]+)#i', (string) $request->getUrl(), $urlTokens)) {
            return null;
        }

        return $urlTokens['template_id'];
    }

    private function extractDataVersionFromRequest(HttpRequest $request): string
    {
        $dataFromRequest = $this->extractDataFromRequest($request);
        if (! ($this->hasValue($dataFromRequest, 'data_version'))) {
            throw new InvalidTemplateApiRequestBodyException('The API request is missing the target data_version.');
        }

        return (string) $dataFromRequest['data_version'];
    }

    private function extractContentFromRequest(HttpRequest $request): string
    {
        return (string) ($this->extractDataFromRequest($request)['content'] ?? '');
    }

    /**
     * @param mixed[] $dataFromRequest
     * @param string $key
     * @return bool
     */
    private function hasValue(array $dataFromRequest, string $key): bool
    {
        return is_array($dataFromRequest) && isset($dataFromRequest[$key]);
    }

    private function extractDataFromRequest(HttpRequest $request)
    {
        $decodedData = json_decode($request->getRawBody(), true);
        if (json_last_error()) {
            $message = 'The request body is not valid JSON: ' . json_last_error_msg();
            throw new InvalidTemplateApiRequestBodyException($message);
        }

        return $decodedData;
    }
}
