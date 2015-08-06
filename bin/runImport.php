<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpRouterChain;
use Brera\Http\HttpUrl;

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

    /**
     * @return LogMessage[]
     */
    public function getLoggedMessages()
    {
        return $this->getMasterFactory()->getLogger()->getMessages();
    }
}


$httpRequestBodyContent = file_get_contents(__DIR__ . '/../tests/shared-fixture/product-listing-root-snippet.xml');
$productListingImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/page_templates/product_listing'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.page_templates.v1+json']),
    HttpRequestBody::fromString($httpRequestBodyContent)
);
$productListingImport = new ApiApp($productListingImportRequest);
$productListingImport->runWithoutSendingResponse();


$catalogImportRequest = HttpRequest::fromParameters(
    HttpRequest::METHOD_PUT,
    HttpUrl::fromString('http://example.com/api/catalog_import'),
    HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']),
    HttpRequestBody::fromString(json_encode(['fileName' => 'catalog.xml']))
);
$catalogImport = new ApiApp($catalogImportRequest);
$catalogImport->runWithoutSendingResponse();


$catalogImport->processQueues();


$messages = array_merge($productListingImport->getLoggedMessages(), $catalogImport->getLoggedMessages());
if (count($messages) > 0) {
    echo "Log message(s):\n";
    foreach ($messages as $message) {
        printf("\t%s\n", rtrim($message));
    }
}
