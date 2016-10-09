<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

/**
 * @covers \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpRequestBodyTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsTheRequestBodyAsString()
    {
        $requestContent = 'the request content';
        $requestBody = new HttpRequestBody($requestContent);
        $this->assertSame($requestContent, $requestBody->toString());
    }
}
