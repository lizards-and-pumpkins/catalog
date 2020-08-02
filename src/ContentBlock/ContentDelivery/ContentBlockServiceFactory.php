<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Core\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class ContentBlockServiceFactory implements FactoryWithCallback
{
    use FactoryWithCallbackTrait;

    public function factoryRegistrationCallback(MasterFactory $masterFactory): void
    {
        $apiVersion = 2;

        /** @var ApiRequestHandlerLocator $handlerLocator */
        $handlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $handlerLocator->register('get_' . ContentBlockApiV2GetRequestHandler::ENDPOINT, $apiVersion, function () {
            return $this->getMasterFactory()->createContentBlockApiV2GetRequestHandler();
        });
    }

    public function createContentBlockApiV2GetRequestHandler(): ContentBlockApiV2GetRequestHandler
    {
        return new ContentBlockApiV2GetRequestHandler(
            $this->getMasterFactory()->getContentBlockService(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    public function getContentBlockService(): ContentBlockService
    {
        return new ContentBlockService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy()
        );
    }
}
