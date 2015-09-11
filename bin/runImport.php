#!/usr/bin/env php
<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpRouterChain;
use Brera\Http\HttpUrl;
use Brera\Log\Writer\CompositeLogMessageWriter;
use Brera\Log\Writer\FileLogMessageWriter;
use Brera\Log\Writer\LogMessageWriter;
use Brera\Log\Writer\StdOutLogMessageWriter;
use Brera\Queue\File\FileQueue;
use Brera\Queue\LoggingQueueDecorator;
use Brera\Queue\Queue;
use Brera\Utils\Clearable;

require_once __DIR__ . '/../vendor/autoload.php';

class ApiApp extends WebFront
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return new SampleMasterFactory();
    }

    protected function registerFactories(MasterFactory $factory)
    {
        $factory->register(new CommonFactory());
        $factory->register(new SampleFactory());
        $factory->register(new LoggingQueueFactory());
        $factory->register(new FrontendFactory($this->getRequest()));
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
    
    public function processQueues()
    {
        $commandConsumer = $this->getMasterFactory()->createCommandConsumer();
        $commandConsumer->process();
        $domainEventConsumer = $this->getMasterFactory()->createDomainEventConsumer();
        $domainEventConsumer->process();
    }

    public function clearStorage()
    {
        $this->getMasterFactory();
        $this->getMasterFactory()->createDataPoolWriter()->clear();
        $this->getMasterFactory()->createCommandQueue()->clear();
        $this->getMasterFactory()->createEventQueue()->clear();
    }
}

class LoggingQueueFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return Queue|Clearable
     */
    public function createEventQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/event-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/event-queue/lock';
        return new LoggingQueueDecorator(
            new FileQueue($storagePath, $lockFile),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return Queue|Clearable
     */
    public function createCommandQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/command-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/command-queue/lock';
        return new LoggingQueueDecorator(
            new FileQueue($storagePath, $lockFile),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return LogMessageWriter
     */
    public function createLogMessageWriter()
    {
        return new CompositeLogMessageWriter(
            new StdOutLogMessageWriter(),
            new FileLogMessageWriter($this->getMasterFactory()->getLogFilePathConfig())
        );
    }
}




$httpRequestBodyContent = file_get_contents(__DIR__ . '/../tests/shared-fixture/product-listing-root-snippet.json');
$productListingImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/templates/product_listing'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.templates.v1+json']),
    HttpRequestBody::fromString($httpRequestBodyContent)
);
$productListingImport = new ApiApp($productListingImportRequest);
$productListingImport->runWithoutSendingResponse();


$productSearchAutosuggestionImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/templates/product_search_autosuggestion'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.templates.v1+json']),
    HttpRequestBody::fromString('')
);
$productSearchAutosuggestionImport = new ApiApp($productSearchAutosuggestionImportRequest);
$productSearchAutosuggestionImport->runWithoutSendingResponse();


$catalogImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/catalog_import'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']),
    HttpRequestBody::fromString(json_encode(['fileName' => 'catalog-seed.xml']))
);

$catalogImport = new ApiApp($catalogImportRequest);
$catalogImport->clearStorage();
$catalogImport->runWithoutSendingResponse();

//$catalogImport->processQueues();
