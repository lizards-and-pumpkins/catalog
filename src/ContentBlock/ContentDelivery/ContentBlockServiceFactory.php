<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ContentBlockServiceFactory implements FactoryWithCallback
{
    use FactoryWithCallbackTrait;

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        $apiVersion = 2;

        /** @var ApiRequestHandlerLocator $handlerLocator */
        $handlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $handlerLocator->register('get_' . ContentBlockApiV2GetRequestHandler::ENDPOINT, $apiVersion, function () {
            return $this->getMasterFactory()->createContentBlockApiV2GetRequestHandler();
        });
    }

    public function createContentBlockApiV2GetRequestHandler()
    {
        return new ContentBlockApiV2GetRequestHandler(
            $this->getMasterFactory()->getContentBlockService(),
            $this->getMasterFactory()->createContextBuilder()
        );
    }

    public function getContentBlockService()
    {
        return new ContentBlockService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy()
        );
    }
}
