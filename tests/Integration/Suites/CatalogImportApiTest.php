<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogImportApiTest extends AbstractIntegrationTest
{
    const TIMEOUT = 5;

    /**
     * @var \Closure
     */
    private $checkTimeout;

    private function getNextMessageFromQueue(Queue $queue) : Message
    {
        $receiver = new class implements MessageReceiver
        {
            public $message;

            public function receive(Message $message)
            {
                $this->message = $message;
            }
        };
        declare(ticks=1) {
            $queue->consume($receiver, 1);
        }

        return $receiver->message;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->setupTimeOutToNoticeNoMessageAdded();
    }

    protected function setupTimeOutToNoticeNoMessageAdded()
    {
        $startTime = microtime(true);
        $this->checkTimeout = function () use ($startTime) {
            if ((microtime(true) - $startTime) > 5) {
                throw new \Exception('Tests ran longer than ' . self::TIMEOUT . ' sec, it seems no message was added ' .
                    'to the queue and the endless loop in \LizardsAndPumpkins\Messaging\Queue::consume was killed.');

            }
        };
        register_tick_function($this->checkTimeout);
    }

    protected function tearDown()
    {
        unregister_tick_function($this->checkTimeout);
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

        $website = new InjectableDefaultWebFront($request, $factory, $implementationSpecificFactory);
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

        $website = new InjectableDefaultWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $message = $this->getNextMessageFromQueue($commandQueue);
        $this->assertSame(ImportCatalogCommand::CODE, $message->getName());
        $this->assertSame($testDataVersionString, $message->getMetadata()['data_version']);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
