<?php


namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\InvalidHttpRequestBodyException;

/**
 * @covers \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpRequestBodyTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsTheRequestBodyAsString()
    {
        $requestContent = 'the request content';
        $requestBody = HttpRequestBody::fromString($requestContent);
        $this->assertSame($requestContent, $requestBody->toString());
    }

    public function testItThrowsAnExceptionIfANonStringIsSpecified()
    {
        $this->setExpectedException(InvalidHttpRequestBodyException::class);
        HttpRequestBody::fromString([]);
    }
}
