<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ProductListingTemplateProjectionTest extends AbstractIntegrationTest
{
    private function createTemplatesApiRequestHeadersForVersion($version): HttpHeaders
    {
        return HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v' . $version . '+json',
        ]);
    }

    private function processRequest(CatalogMasterFactory $factory, HttpRequest $request): void
    {
        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $website->processRequest();
        $this->processAllMessages($factory);
        $this->failIfMessagesWhereLogged($factory->getLogger());
    }

    private function createContextWithVersion(CatalogMasterFactory $factory, string $versionString): Context
    {
        return $factory->createContextBuilder()->createContext(array_merge(
            $factory->createContext()->jsonSerialize(),
            [DataVersion::CONTEXT_CODE => $versionString]
        ));
    }

    private function buildProductListingTemplatesApiHttpRequestForV1(): HttpRequest
    {
        return $this->buildProductListingTemplatesApiHttpRequest('1', new HttpRequestBody(''));
    }

    private function buildProductListingTemplatesApiHttpRequestForV2WithTargetDataVersion(string $version): HttpRequest
    {
        $requestBodyContent = json_encode(['data_version' => $version]);

        return $this->buildProductListingTemplatesApiHttpRequest('2', new HttpRequestBody($requestBodyContent));
    }

    private function buildProductListingTemplatesApiHttpRequest(string $version, HttpRequestBody $body): HttpRequest
    {
        $httpUrl = HttpUrl::fromString('https://example.com/api/templates/product_listing');
        $httpHeaders = $this->createTemplatesApiRequestHeadersForVersion($version);

        return HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $body);
    }

    private function getProductListingTemplateSnippetKey(CatalogMasterFactory $factory, string $version): string
    {
        $context = $this->createContextWithVersion($factory, $version);
        $productListingTemplateSnippetKeyGenerator = $factory->createProductListingTemplateSnippetKeyGenerator();

        return $productListingTemplateSnippetKeyGenerator->getKeyForContext($context, []);
    }

    private function assertHasProductListingTemplateForDataVersion(CatalogMasterFactory $factory, string $version): void
    {
        $key = $this->getProductListingTemplateSnippetKey($factory, $version);

        $message = sprintf('No product listing root template with version %s in data pool.', $version);
        $this->assertTrue($factory->createDataPoolReader()->hasSnippet($key), $message);
    }

    private function assertNotHasProductListingTemplateForDataVersion(CatalogMasterFactory $factory, string $version): void
    {
        $key = $this->getProductListingTemplateSnippetKey($factory, $version);

        $message = sprintf('Product listing root template with version %s found in data pool.', $version);
        $this->assertFalse($factory->createDataPoolReader()->hasSnippet($key), $message);
    }

    public function testV1TemplatesApiHandlerProjectsWithCurrentDataVersion(): void
    {
        $currentVersionForTest = 'foo';

        $request = $this->buildProductListingTemplatesApiHttpRequestForV1();
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $factory->createDataPoolWriter()->setCurrentDataVersion($currentVersionForTest);

        $this->processRequest($factory, $request);

        $context = $this->createContextWithVersion($factory, $currentVersionForTest);

        $productListingTemplateSnippetKeyGenerator = $factory->createProductListingTemplateSnippetKeyGenerator();
        $key = $productListingTemplateSnippetKeyGenerator->getKeyForContext($context, []);
        $this->assertTrue($factory->createDataPoolReader()->hasSnippet($key));
    }

    public function testV2TemplatesApiHandlerProjectsWithCurrentDataVersion(): void
    {
        $currentVersion = 'foo';
        $projectionVersion = 'bar';

        $request = $this->buildProductListingTemplatesApiHttpRequestForV2WithTargetDataVersion($projectionVersion);
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $factory->createDataPoolWriter()->setCurrentDataVersion($currentVersion);

        $this->processRequest($factory, $request);

        $this->assertHasProductListingTemplateForDataVersion($factory, $projectionVersion);
        $this->assertNotHasProductListingTemplateForDataVersion($factory, $currentVersion);
    }
}
