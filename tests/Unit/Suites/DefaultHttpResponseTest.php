<?php

namespace Brera;

use Brera\Http\HttpResponse;
use Brera\Http\InvalidResponseBodyException;

/**
 * @covers \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
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
        $this->setExpectedException(InvalidResponseBodyException::class);
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
     */
    public function testGivenHeaderIsIncludedIntoResponse()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('This test requires the PHP extension xdebug to be installed.');
        }

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
