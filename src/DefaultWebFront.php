<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUnknownMethodRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodHandler;
use LizardsAndPumpkins\Http\Routing\WebRequestHandlerLocator;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Exception\MalformedMetaSnippetException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Logging\LoggingQueueFactory;
use LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchSharedFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class DefaultWebFront extends WebFront
{
    protected function createMasterFactory(): MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new ProductSearchSharedFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
        $masterFactory->register($this->getImplementationSpecificFactory());
        //$this->enableDebugLogging($masterFactory, $commonFactory, $implementationFactory);
    }

    private function enableDebugLogging(MasterFactory $masterFactory, CommonFactory $commonFactory)
    {
        $masterFactory->register(new LoggingQueueFactory($this->getImplementationSpecificFactory()));
        $masterFactory->register(new LoggingCommandHandlerFactory($commonFactory));
        $masterFactory->register(new LoggingDomainEventHandlerFactory($commonFactory));
    }

    final protected function getHandlerForRequest(HttpRequest $request): HttpRequestHandler
    {
        if ($request instanceof HttpUnknownMethodRequest) {
            return $this->getWebRequestHandlerLocator()->getRequestHandlerForCode(
                UnknownHttpRequestMethodHandler::CODE,
                ''
            );
        }

        $metaJson = $this->getMetaJson($request);
        $requestHandlerCode = $this->getRequestHandlerCode($request, $metaJson);

        return $this->getWebRequestHandlerLocator()->getRequestHandlerForCode($requestHandlerCode, $metaJson);
    }

    private function getRequestHandlerCode(HttpRequest $request, string $metaInfoJson): string
    {
        $metaData = json_decode($metaInfoJson, true);

        if (! isset($metaData[PageMetaInfoSnippetContent::KEY_HANDLER_CODE])) {
            throw new MalformedMetaSnippetException('Request handler code is missing in meta snippet.');
        }

        $requestHandlerCode = $metaData[PageMetaInfoSnippetContent::KEY_HANDLER_CODE];

        if ($requestHandlerCode === ProductSearchRequestHandler::CODE && ! $this->isValidSearchRequest($request)) {
            return ResourceNotFoundRequestHandler::CODE;
        }

        return $requestHandlerCode;
    }

    private function getMetaJson(HttpRequest $request): string
    {
        $urlKey = $this->getPathWithoutPrefix($request);

        try {
            return $this->getDataPoolReader()->getPageMetaSnippet($urlKey, $this->getMasterFactory()->createContext());
        } catch (KeyNotFoundException $exception) {
            return json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => ResourceNotFoundRequestHandler::CODE]);
        }
    }

    private function isValidSearchRequest(HttpRequest $request): bool
    {
        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        if (! $request->hasQueryParameter(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME) ||
            strlen((string) $request->getQueryParameter(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)) < 1
        ) {
            return false;
        }

        return true;
    }

    private function getPathWithoutPrefix(HttpRequest $request): string
    {
        return $this->getUrlToWebsiteMap()->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
    }

    private function getDataPoolReader(): DataPoolReader
    {
        return $this->getMasterFactory()->createDataPoolReader();
    }

    private function getWebRequestHandlerLocator(): WebRequestHandlerLocator
    {
        return $this->getMasterFactory()->createWebRequestHandlerLocator();
    }

    private function getUrlToWebsiteMap(): UrlToWebsiteMap
    {
        return $this->getMasterFactory()->createUrlToWebsiteMap();
    }
}
