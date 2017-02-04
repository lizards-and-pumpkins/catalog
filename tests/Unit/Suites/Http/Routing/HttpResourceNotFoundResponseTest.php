<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\HttpResourceNotFoundResponse
 */
class HttpResourceNotFoundResponseTest extends TestCase
{
    public function test404ResponseCodeIsSet()
    {
        ob_start();
        (new HttpResourceNotFoundResponse())->send();
        ob_end_clean();

        $this->assertEquals(404, http_response_code());
    }
}
