<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnableToProcessProductSearchRequestException;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

class ProductSearchApiV1GetRequestHandler extends ApiRequestHandler
{
    const QUERY_PARAMETER = 'q';

    const NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER = 'limit';

    const PAGE_NUMBER_PARAMETER = 'p';

    const SORT_ORDER_PARAMETER = 'order';

    const SORT_DIRECTION_PARAMETER = 'sort';

    /**
     * @var ProductSearchService
     */
    private $productSearchService;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var int
     */
    private $defaultNumberOfProductPerPage;

    /**
     * @var SortOrderConfig
     */
    private $defaultSortOrderConfig;

    public function __construct(
        ProductSearchService $productSearchService,
        ContextBuilder $contextBuilder,
        int $defaultNumberOfProductPerPage,
        SortOrderConfig $defaultSortOrderConfig
    ) {
        $this->productSearchService = $productSearchService;
        $this->contextBuilder = $contextBuilder;
        $this->defaultNumberOfProductPerPage = $defaultNumberOfProductPerPage;
        $this->defaultSortOrderConfig = $defaultSortOrderConfig;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        if ($request->getMethod() !== HttpRequest::METHOD_GET) {
            return false;
        }

        $parts = $this->getRequestPathParts($request);

        if (count($parts) !== 2 || 'product' !== $parts[1]) {
            return false;
        }

        $query = $request->getQueryParameter(self::QUERY_PARAMETER);

        if (null === $query || '' === trim($query)) {
            return false;
        }

        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        if (! $this->canProcess($request)) {
            throw new UnableToProcessProductSearchRequestException();
        }

        $queryString = $request->getQueryParameter(self::QUERY_PARAMETER);
        $context = $this->contextBuilder->createFromRequest($request);

        $data = $this->productSearchService->query(
            $queryString,
            $context,
            $this->getNumberOfProductPerPage($request),
            $this->getPageNumber($request),
            $this->getSortOrderConfig($request)
        );

        $body = json_encode($data);
        $headers = [];

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_OK);
    }

    /**
     * @param HttpRequest $request
     * @return string[]
     */
    private function getRequestPathParts(HttpRequest $request) : array
    {
        return explode('/', trim($request->getPathWithoutWebsitePrefix(), '/'));
    }

    private function getNumberOfProductPerPage(HttpRequest $request) : int
    {
        $requestedNumberOfProductsPerPage = $request->getQueryParameter(self::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER);

        if (null !== $requestedNumberOfProductsPerPage) {
            return (int) $requestedNumberOfProductsPerPage;
        }

        return $this->defaultNumberOfProductPerPage;
    }

    private function getPageNumber(HttpRequest $request) : int
    {
        $requestedPageNumber = $request->getQueryParameter(self::PAGE_NUMBER_PARAMETER);

        if (null !== $requestedPageNumber) {
            return (int) $requestedPageNumber;
        }

        return 0;
    }

    private function getSortOrderConfig(HttpRequest $request) : SortOrderConfig
    {
        $requestedSortOrder = $request->getQueryParameter(self::SORT_ORDER_PARAMETER);

        if (null !== $requestedSortOrder) {
            $sortDirection = $this->getSortDirectionString($request);

            return SortOrderConfig::create(
                AttributeCode::fromString($requestedSortOrder),
                SortOrderDirection::create($sortDirection)
            );
        }

        return $this->defaultSortOrderConfig;
    }

    private function getSortDirectionString(HttpRequest $request) : string
    {
        $requestedSortDirection = $request->getQueryParameter(self::SORT_DIRECTION_PARAMETER);

        if (null !== $requestedSortDirection) {
            return $requestedSortDirection;
        }

        return SortOrderDirection::ASC;
    }
}
