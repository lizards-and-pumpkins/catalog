<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpRequestBodyTest extends TestCase
{
    public function testItReturnsTheRequestBodyAsString()
    {
        $requestContent = 'the request content';
        $requestBody = new HttpRequestBody($requestContent);
        $this->assertSame($requestContent, $requestBody->toString());
    }

    public function testItThrowsAnExceptionIfANonStringIsSpecified()
    {
        $this->expectException(\TypeError::class);
        new HttpRequestBody([]);
    }
}
