<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpResourceNotFoundResponse
 */
class HttpResourceNotFoundResponseTest extends \PHPUnit_Framework_TestCase
{
    public function test404ResponseCodeIsSet()
    {
        ob_start();
        (new HttpResourceNotFoundResponse())->send();
        ob_end_clean();

        $this->assertEquals(404, http_response_code());
    }
}
