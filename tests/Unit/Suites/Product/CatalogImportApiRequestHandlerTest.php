<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\CatalogImportApiRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 */
class CatalogImportApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testResponseContainsExpectedJson()
    {
        $response = (new CatalogImportApiRequestHandler())->process();
        $result = json_decode($response->getBody());
        $expectedJson = 'dummy response';

        $this->assertEquals($expectedJson, $result);
    }
}
