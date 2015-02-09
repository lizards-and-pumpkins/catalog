<?php

namespace Brera;

use Brera\Product\CatalogImportApiRequestHandler;

/**
 * @covers \Brera\FrontendFactory
 * @covers \Brera\FactoryTrait
 */
class FrontendFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    public function setUp()
    {
        $this->frontendFactory = new FrontendFactory();
    }

    /**
     * @test
     * @expectedException \Brera\NoMasterFactorySetException
     */
    public function itShouldThrowAnExceptionIfNoMasterFactoryIsSet()
    {
     /*
      * The getMasterFactory method is protected so other method which will trigger it is called.
      */
        $this->frontendFactory->createApiRouter();
    }

    /**
     * @test
     */
    public function itShouldReturnCatalogImportApiRequestHandler()
    {
        $result = $this->frontendFactory->createCatalogImportApiRequestHandler();

        $this->assertInstanceOf(CatalogImportApiRequestHandler::class, $result);
    }
}
