<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidStatusCodeException;
use LizardsAndPumpkins\Http\HttpResponse;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class GenericHttpResponseTest extends TestCase
{
    public function testHttpResponseInterfaceIsImplemented()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = HttpResponse::STATUS_OK;

        $result = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testExceptionIsThrownDuringAttemptToCreateResponseWithNonStringBody()
    {
        $invalidBody = 1;
        $dummyHeaders = [];
        $dummyStatusCode = HttpResponse::STATUS_OK;

        $this->expectException(\TypeError::class);

        GenericHttpResponse::create($invalidBody, $dummyHeaders, $dummyStatusCode);
    }

    public function testExceptionIsThrownDuringAttemptToCreateResponseWithNonIntegerStatusCode()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $invalidStatusCode = false;

        $this->expectException(\TypeError::class);

        GenericHttpResponse::create($dummyBody, $dummyHeaders, $invalidStatusCode);
    }

    public function testExceptionIsThrownIfGivenResponseStatusCodeIsOutOfRange()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $invalidStatusCode = 104;

        $this->expectException(InvalidStatusCodeException::class);
        $this->expectExceptionMessage(sprintf('Response status code %s is not supported.', $invalidStatusCode));

        GenericHttpResponse::create($dummyBody, $dummyHeaders, $invalidStatusCode);
    }

    public function testResponseBodyIsReturned()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = HttpResponse::STATUS_OK;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);
        $result = $response->getBody();

        $this->assertEquals($dummyBody, $result);
    }

    public function testBodyIsEchoed()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = HttpResponse::STATUS_OK;

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
        $dummyStatusCode = HttpResponse::STATUS_OK;

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
        $dummyStatusCode = HttpResponse::STATUS_NOT_FOUND;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        $this->assertSame($dummyStatusCode, $response->getStatusCode());
    }

    public function testDefinedResponseCodeIsSet()
    {
        $dummyBody = 'foo';
        $dummyHeaders = [];
        $dummyStatusCode = HttpResponse::STATUS_ACCEPTED;

        $response = GenericHttpResponse::create($dummyBody, $dummyHeaders, $dummyStatusCode);

        ob_start();
        $response->send();
        ob_end_clean();

        $this->assertEquals($dummyStatusCode, http_response_code());
    }
}
