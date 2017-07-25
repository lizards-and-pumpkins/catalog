<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SnippetReader;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\Exception\MalformedMetaSnippetException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;

class MetaSnippetBasedRouter implements HttpRouter
{
    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    /**
     * @var SnippetReader
     */
    private $snippetReader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var callable[]
     */
    private $requestHandlerRegistry = [];

    public function __construct(UrlToWebsiteMap $urlToWebsiteMap, SnippetReader $snippetReader, Context $context)
    {
        $this->urlToWebsiteMap = $urlToWebsiteMap;
        $this->snippetReader = $snippetReader;
        $this->context = $context;
    }

    public function registerHandlerCallback(string $requestHandlerCode, callable $callbackFunction)
    {
        $this->requestHandlerRegistry[$requestHandlerCode] = $callbackFunction;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        $metaJson = $this->getMetaJson($request);

        if ('' === $metaJson) {
            return null;
        }

        $requestHandlerCode = $this->getRequestHandlerCode($metaJson);

        if (! $this->isRequestHandlerRegistered($requestHandlerCode)) {
            return null;
        }

        $requestHandler = $this->getRequestHandler($requestHandlerCode, $metaJson);

        if (! $requestHandler->canProcess($request)) {
            return null;
        }
        
        return $requestHandler;
    }

    private function getMetaJson(HttpRequest $request): string
    {
        $urlKey = $this->getPathWithoutPrefix($request);

        try {
            return $this->snippetReader->getPageMetaSnippet($urlKey, $this->context);
        } catch (KeyNotFoundException $exception) {
            return '';
        }
    }

    private function isRequestHandlerRegistered(string $requestHandlerCode): bool
    {
        return isset($this->requestHandlerRegistry[$requestHandlerCode]);
    }

    private function getRequestHandler(string $requestHandlerCode, string $metaJson): HttpRequestHandler
    {
        return $this->requestHandlerRegistry[$requestHandlerCode]($metaJson);
    }

    private function getRequestHandlerCode(string $metaInfoJson): string
    {
        $metaData = json_decode($metaInfoJson, true);
        if (! isset($metaData[PageMetaInfoSnippetContent::KEY_HANDLER_CODE])) {
            throw new MalformedMetaSnippetException('Request handler code is missing in meta snippet.');
        }

        return $metaData[PageMetaInfoSnippetContent::KEY_HANDLER_CODE];
    }

    private function getPathWithoutPrefix(HttpRequest $request): string
    {
        return rtrim($this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl()), '/');
    }
}
