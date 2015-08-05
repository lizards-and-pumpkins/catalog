<?php

namespace Brera;

use Brera\Http\HttpRequest;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactory(HttpRequest $request)
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        $factory->register(new FrontendFactory($request));
        return $factory;
    }

    final protected function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (!empty($messages)) {
            $messageString = implode(PHP_EOL, $messages);
            $this->fail($messageString);
        }
    }
}
