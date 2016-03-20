<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class ProductUrlKeyStoreTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    /**
     * @param string $importFileName
     */
    private function importCatalog($importFileName)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    public function testUrlKeysAreWrittenToStore()
    {
        $this->importCatalog('catalog.xml');

        $this->failIfMessagesWhereLogged($this->factory->getLogger());
        
        $dataPoolReader = $this->factory->createDataPoolReader();

        $currentVersion = $dataPoolReader->getCurrentDataVersion();
        $urlKeys = $dataPoolReader->getUrlKeysForVersion($currentVersion);
        $this->assertNotEmpty($urlKeys);
    }
}
