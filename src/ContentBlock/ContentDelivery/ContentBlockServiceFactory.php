<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * Class ContentBlockServiceFactory
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 */
class ContentBlockServiceFactory implements FactoryWithCallback
{
    const API_VERSION          = 2;
    const REQUEST_HANDLER_CODE = 'get_content_block';

    use FactoryWithCallbackTrait;

    /**
     * @param MasterFactory $masterFactory
     *
     * @return void
     */
    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        /** @var ApiRequestHandlerLocator $handlerLocator */
        $handlerLocator = $masterFactory->getApiRequestHandlerLocator();
        $handlerLocator->register(
            self::REQUEST_HANDLER_CODE,
            self::API_VERSION,
            function () {
                return $this->getMasterFactory()->createContentBlockApiV2GetRequestHandler();
            }
        );
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