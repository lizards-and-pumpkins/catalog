<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;

class CatalogImportApiTest extends AbstractIntegrationTest
{
    private function getNextMessageFromQueue(Queue $queue) : Message
    {
        $receiver = new class implements MessageReceiver
        {
            public $message;

            public function receive(Message $message): void
            {
                $this->message = $message;
            }
        };
        $queue->consume($receiver, 1);

        return $receiver->message;
    }

    public function testV1CatalogImportHandlerPlacesImportCommandsIntoQueue()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json',
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);

        $commandQueue = $factory->getCommandMessageQueue();
        $this->assertEquals(0, $commandQueue->count());

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $message = $this->getNextMessageFromQueue($commandQueue);
        $this->assertSame('import_catalog', $message->getName());
        $this->assertSame('-1', $message->getMetadata()['data_version']);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }

    public function testV2CatalogImportHandlerPlacesImportCommandsIntoQueue()
    {
        $testDataVersionString = 'foo-123';
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v2+json',
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml', 'dataVersion' => $testDataVersionString]);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);

        $commandQueue = $factory->getCommandMessageQueue();
        $this->assertEquals(0, $commandQueue->count());

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $message = $this->getNextMessageFromQueue($commandQueue);
        $this->assertSame(ImportCatalogCommand::CODE, $message->getName());
        $this->assertSame($testDataVersionString, $message->getMetadata()['data_version']);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
