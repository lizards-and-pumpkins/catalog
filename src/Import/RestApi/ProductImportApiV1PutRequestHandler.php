<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportProductDataNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\RestApi\Exception\DataVersionNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use LizardsAndPumpkins\RestApi\RestApiRequestHandler;

class ProductImportApiV1PutRequestHandler implements RestApiRequestHandler
{
    /**
     * @var ProductJsonToXml
     */
    private $productJsonToXml;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    public function __construct(ProductJsonToXml $productJsonToXml, CatalogImport $catalogImport)
    {
        $this->productJsonToXml = $productJsonToXml;
        $this->catalogImport = $catalogImport;
    }

    public function canProcess(HttpRequest $request): bool
    {
        return $request->getMethod() === HttpRequest::METHOD_PUT;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        $productData = $this->getProductDataFromRequest($request);
        $productXml = $this->productJsonToXml->toXml($productData);

        $dataVersion = $this->createDataVersion($request);

        $this->catalogImport->addProductsAndProductImagesToQueue($productXml, $dataVersion);

        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_ACCEPTED);
    }

    private function getProductDataFromRequest(HttpRequest $request): string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (! $this->hasArgument($requestArguments, 'product_data')) {
            throw new CatalogImportProductDataNotFoundInRequestBodyException(
                'Product data not found in import product API request.'
            );
        }

        return $requestArguments['product_data'];
    }

    /**
     * @param string[] $requestArguments
     * @param string $argument
     * @return bool
     */
    private function hasArgument($requestArguments, string $argument): bool
    {
        return is_array($requestArguments) && isset($requestArguments[$argument]) && $requestArguments[$argument];
    }

    private function createDataVersion(HttpRequest $request): DataVersion
    {
        $versionString = $this->getDataVersionFromRequest($request);

        return DataVersion::fromVersionString($versionString);
    }

    private function getDataVersionFromRequest(HttpRequest $request): string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (! $this->hasArgument($requestArguments, 'data_version')) {
            throw new DataVersionNotFoundInRequestBodyException(
                'The catalog import data version is not found in request body.'
            );
        }

        return $requestArguments['data_version'];
    }
}
