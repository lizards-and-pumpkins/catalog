<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportProductDataNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

class ProductImportApiV1PutRequestHandler extends ApiRequestHandler
{
    /**
     * @var ProductJsonToXml
     */
    private $productJsonToXml;

    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(ProductJsonToXml $productJsonToXml, CatalogImport $catalogImport, DataVersion $dataVersion)
    {
        $this->productJsonToXml = $productJsonToXml;
        $this->catalogImport = $catalogImport;
        $this->dataVersion = $dataVersion;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        return $request->getMethod() === HttpRequest::METHOD_PUT;
    }

    public function processRequest(HttpRequest $request) : HttpResponse
    {
        $productData = $this->getProductDataFromRequest($request);
        $productXml = $this->productJsonToXml->toXml($productData);

        $this->catalogImport->addProductsAndProductImagesToQueue($productXml, $this->dataVersion);

        return $this->getResponse($request);
    }

    private function getProductDataFromRequest(HttpRequest $request) : string
    {
        $requestArguments = json_decode($request->getRawBody(), true);

        if (! $this->hasArgument($requestArguments, 'product_data')) {
            throw new CatalogImportProductDataNotFoundInRequestBodyException(
                'Product data not found in import product API request.'
            );
        }

        return $requestArguments['product_data'];
    }

    private function hasArgument($requestArguments, string $argument) : bool
    {
        return is_array($requestArguments) && isset($requestArguments[$argument]) && $requestArguments[$argument];
    }

    protected function getResponse(HttpRequest $request) : HttpResponse
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }
}
