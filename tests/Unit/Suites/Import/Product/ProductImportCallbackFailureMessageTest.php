<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Logging\LogMessage;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ProductImportCallbackFailureMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductImportCallbackFailureMessage
     */
    private $logMessage;

    /**
     * @var \Exception
     */
    private $testException;

    private $testInvalidXml = 'invalid';

    protected function setUp()
    {
        $this->testException = new \Exception('Test Message');
        $this->logMessage = new ProductImportCallbackFailureMessage($this->testException, $this->testInvalidXml);
    }
    
    public function testItImplementsLogMessage()
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testItReturnsTheExceptionMessage()
    {
        $expected = 'Error during processing catalog product XML import for product "- unknown -": Test Message';
        $this->assertSame($expected, (string) $this->logMessage);
    }

    public function testItIncludesTheProductXmlInTheContextArray()
    {
        $this->assertInternalType('array', $this->logMessage->getContext());
        $this->assertArrayHasKey('product_xml', $this->logMessage->getContext());
        $this->assertSame($this->testInvalidXml, $this->logMessage->getContext()['product_xml']);
    }

    public function testItIncludesTheExceptionInTheContextArray()
    {
        $this->assertArrayHasKey('exception', $this->logMessage->getContext());
        $this->assertSame($this->testException, $this->logMessage->getContext()['exception']);
    }

    public function testItExtractsTheProductSkuFromTheXmlFragment()
    {
        $productXml = '<product sku="test-id"></product>';
        $logMessage = new ProductImportCallbackFailureMessage($this->testException, $productXml);
        $expected = 'Error during processing catalog product XML import for product "test-id": Test Message';
        $this->assertSame($expected, (string) $logMessage);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis()
    {
        $synopsis = $this->logMessage->getContextSynopsis();
        $this->assertContains($this->testException->getFile(), $synopsis);
        $this->assertContains((string) $this->testException->getLine(), $synopsis);
    }
}
