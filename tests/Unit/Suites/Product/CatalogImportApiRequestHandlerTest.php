<?php

namespace Brera\Product;

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
        $response = $this->apiRequestHandler->process();
        $result = json_decode($response->getBody());
        $expectedJson = 'dummy response';

        $this->assertEquals($expectedJson, $result);
    }
}
