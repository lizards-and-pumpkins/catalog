<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\ProductRelations\Exception\UnableToProcessProductRelationsRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\ProductId;

class ProductRelationsApiV1GetRequestHandler extends ApiRequestHandler
{
    /**
     * @var ProductRelationsService
     */
    private $productRelationsService;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    public function __construct(
        ProductRelationsService $productRelationsService,
        UrlToWebsiteMap $urlToWebsiteMap,
        ContextBuilder $contextBuilder
    ) {
        $this->productRelationsService = $productRelationsService;
        $this->contextBuilder = $contextBuilder;
        $this->urlToWebsiteMap = $urlToWebsiteMap;
    }

    public function canProcess(HttpRequest $request): bool
    {
        if ($request->getMethod() !== HttpRequest::METHOD_GET) {
            return false;
        }
        // Matching path example: /api/products/example-sku/relations/upsells
        $parts = $this->getRequestPathParts($request);

        return count($parts) > 4 && 'products' === $parts[1] && 'relations' === $parts[3];
    }

    final protected function getResponse(HttpRequest $request): HttpResponse
    {
        if (!$this->canProcess($request)) {
            throw $this->getUnableToProcessRequestException($request);
        }

        $context = $this->contextBuilder->createFromRequest($request);

        $relatedProductsData = $this->productRelationsService->getRelatedProductData(
            $this->getProductRelationTypeCode($request),
            $this->getProductId($request),
            $context
        );

        $headers = [];
        $body = json_encode(['data' => $relatedProductsData]);

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_OK);
    }

    /**
     * @param HttpRequest $request
     * @return string[]
     */
    private function getRequestPathParts(HttpRequest $request): array
    {
        $pathWithoutWebsitePrefix = $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());

        return explode('/', trim($pathWithoutWebsitePrefix, '/'));
    }

    private function getProductId(HttpRequest $request): ProductId
    {
        return new ProductId($this->getRequestPathParts($request)[2]);
    }

    private function getProductRelationTypeCode(HttpRequest $request): ProductRelationTypeCode
    {
        return ProductRelationTypeCode::fromString($this->getRequestPathParts($request)[4]);
    }

    private function getUnableToProcessRequestException(
        HttpRequest $request
    ): UnableToProcessProductRelationsRequestException {
        $requestPath = $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
        $message = sprintf('Unable to process a %s request to "%s"', $request->getMethod(), $requestPath);

        return new UnableToProcessProductRelationsRequestException($message);
    }
}
