<?php

namespace Brera\Product;

use Brera\Http\HttpRequest;

/**
 * @covers \Brera\Product\CatalogImportApiRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 */
class CatalogImportApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogImportApiRequestHandler
     */
    private $apiRequestHandler;

    protected function setUp()
    {
        $this->apiRequestHandler = new CatalogImportApiRequestHandler;
    }

    public function testCanProcessMethodAlwaysReturnsTrue()
    {
        $this->assertTrue($this->apiRequestHandler->canProcess());
    }

    public function testResponseContainsExpectedJson()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $response = $this->apiRequestHandler->process($stubRequest);
        $result = json_decode($response->getBody());
        $expectedJson = 'dummy response';

        $this->assertEquals($expectedJson, $result);
    }
}
