<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\Exception\InvalidResponseBodyException;
use LizardsAndPumpkins\Http\HttpResponse;

/**
 * @covers \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class DefaultHttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testHttpResponseInterfaceIsImplemented()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];

        $result = DefaultHttpResponse::create($dummyBody, $dummyHeaders);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testExceptionIsThrownDuringAttemptToCreateResponseWithNonStringBody()
    {
        $this->expectException(InvalidResponseBodyException::class);
        DefaultHttpResponse::create(1, []);
    }

    public function testResponseBodyIsReturned()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];

        $response = DefaultHttpResponse::create($dummyBody, $dummyHeaders);
        $result = $response->getBody();

        $this->assertEquals($dummyBody, $result);
    }

    public function testBodyIsEchoed()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];

        $response = DefaultHttpResponse::create($dummyBody, $dummyHeaders);
        $response->send();

        $this->expectOutputString($dummyBody);
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testGivenHeaderIsIncludedIntoResponse()
    {
        $customHeaderName = 'foo';
        $customHeaderValue = 'bar';

        $dummyBody = '';
        $dummyHeaders = [$customHeaderName => $customHeaderValue];

        $response = DefaultHttpResponse::create($dummyBody, $dummyHeaders);
        $response->send();

        $expectedHeader = $customHeaderName . ': ' . $customHeaderValue;
        $headers = xdebug_get_headers();

        $this->assertContains($expectedHeader, $headers);
    }
}
