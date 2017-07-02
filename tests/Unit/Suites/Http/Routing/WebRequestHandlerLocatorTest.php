<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\WebRequestHandlerLocator
 */
class WebRequestHandlerLocatorTest extends TestCase
{
    public function testReturnsDefaultCallbackOutput()
    {
        $dummyRequestHandler = $this->createMock(HttpRequestHandler::class);

        $locator = new WebRequestHandlerLocator(function () use ($dummyRequestHandler) {
            return $dummyRequestHandler;
        });

        $this->assertSame($dummyRequestHandler, $locator->getRequestHandlerForCode('handler code', 'meta JSON'));
    }

    public function testReturnsRegisteredRequestHandler()
    {
        $requestHandlerCode = 'foo';

        $locator = new WebRequestHandlerLocator(function () {
        });

        $dummyRequestHandler = $this->createMock(HttpRequestHandler::class);

        $locator->register($requestHandlerCode, function () use ($dummyRequestHandler) {
            return $dummyRequestHandler;
        });

        $this->assertSame($dummyRequestHandler, $locator->getRequestHandlerForCode($requestHandlerCode, 'meta JSON'));
    }
}
