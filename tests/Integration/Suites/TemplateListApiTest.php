<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class TemplateListApiTest extends AbstractIntegrationTest
{
    /**
     * @var MasterFactory
     */
    private $factory;

    private function createGetTemplateRequest(): HttpRequest
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('https://example.com/api/templates'),
            HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json']),
            new HttpRequestBody('')
        );
    }

    private function processRequest(HttpRequest $request): HttpResponse
    {
        $website = new InjectableRestApiWebFront($request, $this->factory, $this->getIntegrationTestFactory($this->factory));

        return $website->processRequest();
    }

    public function testReturnsTemplatesList()
    {
        $expectedTemplateCodes = [
            'product_listing',
            'product_detail_view',
        ];

        $request = $this->createGetTemplateRequest();
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $responseData = json_decode($this->processRequest($request)->getBody(), true);

        $this->assertSame($expectedTemplateCodes, $responseData);

        $this->failIfMessagesWhereLogged($this->factory->getLogger());
    }
}
