<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\Exception\UnableToProcessProductRelationsRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Product\ProductId;

class ProductRelationsApiV1RequestHandler extends ApiRequestHandler
{
    /**
     * @var ProductRelationsService
     */
    private $productRelationsService;

    public function __construct(ProductRelationsService $productRelationsService)
    {
        $this->productRelationsService = $productRelationsService;
    }
    
    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        if ($request->getMethod() !== HttpRequest::METHOD_GET) {
            return false;
        }
        $parts = $this->getRequestPathParts($request);
        // Matching path example: /api/products/example-sku/relations/upsells
        return count($parts) > 4 && 'products' === $parts[1] && 'relations' === $parts[3];
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
    {
        if (! $this->canProcess($request)) {
            $requestPath = $request->getUrlPathRelativeToWebFront();
            $message = sprintf('Unable to process a %s request to "%s"', $request->getMethod(), $requestPath);
            throw new UnableToProcessProductRelationsRequestException($message);
        }
        $relatedProducts = $this->productRelationsService->getRelatedProductData(
            $this->getProductRelationTypeCode($request),
            $this->getProductId($request)
        );
        return json_encode($relatedProducts);
    }

    /**
     * @param HttpRequest $request
     * @return string[]
     */
    private function getRequestPathParts(HttpRequest $request)
    {
        return explode('/', trim($request->getUrlPathRelativeToWebFront(), '/'));
    }

    /**
     * @param HttpRequest $request
     * @return ProductId
     */
    private function getProductId(HttpRequest $request)
    {
        return ProductId::fromString($this->getRequestPathParts($request)[2]);
    }

    /**
     * @param HttpRequest $request
     * @return ProductRelationTypeCode
     */
    protected function getProductRelationTypeCode(HttpRequest $request)
    {
        return ProductRelationTypeCode::fromString($this->getRequestPathParts($request)[4]);
    }
}
