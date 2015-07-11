<?php

namespace Brera;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactory()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        $factory->register(new FrontendFactory());
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
