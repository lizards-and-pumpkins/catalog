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

    public function route(HttpRequest $request): ?HttpRequestHandler
    {
        $pageMeta = $this->getPageMeta($request);

        if ([] === $pageMeta) {
            return null;
        }

        $requestHandlerCode = $this->getRequestHandlerCode($pageMeta);

        if (! $this->isRequestHandlerRegistered($requestHandlerCode)) {
            return null;
        }

        $requestHandler = $this->getRequestHandler($requestHandlerCode, $pageMeta);

        if (! $requestHandler->canProcess($request)) {
            return null;
        }
        
        return $requestHandler;
    }

    /**
     * @param HttpRequest $request
     * @return mixed[]
     */
    private function getPageMeta(HttpRequest $request): array
    {
        $urlKey = $this->getPathWithoutPrefix($request);

        try {
            return json_decode($this->snippetReader->getPageMetaSnippet($urlKey, $this->context), true);
        } catch (KeyNotFoundException $exception) {
            return [];
        }
    }

    private function isRequestHandlerRegistered(string $requestHandlerCode): bool
    {
        return isset($this->requestHandlerRegistry[$requestHandlerCode]);
    }

    /**
     * @param string $requestHandlerCode
     * @param mixed[] $pageMeta
     * @return HttpRequestHandler
     */
    private function getRequestHandler(string $requestHandlerCode, array $pageMeta): HttpRequestHandler
    {
        return $this->requestHandlerRegistry[$requestHandlerCode]($pageMeta);
    }

    /**
     * @param mixed[] $pageMeta
     * @return string
     */
    private function getRequestHandlerCode(array $pageMeta): string
    {
        if (! isset($pageMeta[PageMetaInfoSnippetContent::KEY_HANDLER_CODE])) {
            throw new MalformedMetaSnippetException('Request handler code is missing in meta snippet.');
        }

        return $pageMeta[PageMetaInfoSnippetContent::KEY_HANDLER_CODE];
    }

    private function getPathWithoutPrefix(HttpRequest $request): string
    {
        return $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
    }
}
