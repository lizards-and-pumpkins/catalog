<?php

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidResponseBodyException;
use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidStatusCodeException;
use LizardsAndPumpkins\Http\HttpResponse;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class GenericHttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testHttpResponseInterfaceIsImplemented()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = 200;

        $result = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testExceptionIsThrownDuringAttemptToCreateResponseWithNonStringBody()
    {
        $invalidBody = 1;
        $dummyHeaders = [];
        $dummyStatusCode = 200;

        $this->expectException(InvalidResponseBodyException::class);
        
        GenericHttpResponse::create($invalidBody, $dummyHeaders, $dummyStatusCode);
    }

    public function testExceptionIsThrownDuringAttemptToCreateResponseWithNonIntegerStatusCode()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $invalidStatusCode = false;

        $this->expectException(InvalidStatusCodeException::class);
        $this->expectExceptionMessage('Response status code must be an integer, got boolean.');
        
        GenericHttpResponse::create($dummyBody, $dummyHeaders, $invalidStatusCode);
    }

    public function testExceptionIsThrownIfGivenResponseStatusCodeIsOutOfRange()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $invalidStatusCode = 600;

        $this->expectException(InvalidStatusCodeException::class);
        $this->expectExceptionMessage('Response status code must be [100-599], got 600.');

        GenericHttpResponse::create($dummyBody, $dummyHeaders, $invalidStatusCode);
    }

    public function testResponseBodyIsReturned()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = 200;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);
        $result = $response->getBody();

        $this->assertEquals($dummyBody, $result);
    }

    public function testBodyIsEchoed()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = 200;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);
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
        $dummyStatusCode = 200;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);
        $response->send();

        $expectedHeader = $customHeaderName . ': ' . $customHeaderValue;
        $headers = xdebug_get_headers();

        $this->assertContains($expectedHeader, $headers);
    }
    
    public function testStatusCodeIsReturned()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = 404;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        $this->assertSame($dummyStatusCode, $response->getStatusCode());
    }

    public function testDefinedResponseCodeIsSet()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = 202;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        ob_start();
        $response->send();
        ob_end_clean();

        $this->assertEquals($dummyStatusCode, http_response_code());
    }
}
