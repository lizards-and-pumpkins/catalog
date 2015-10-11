<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Log\LogMessage;

/**
 * @covers LizardsAndPumpkins\Projection\Catalog\Import\ProductImageImportCallbackFailureMessage
 */
class ProductImageImportCallbackFailureMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductImageImportCallbackFailureMessage
     */
    private $logMessage;

    /**
     * @var \Exception
     */
    private $testException;
    
    private $testInvalidImageXml = '<image/>';

    protected function setUp()
    {
        $this->testException = new \Exception('Test Message');
        $this->logMessage = new ProductImageImportCallbackFailureMessage(
            $this->testException,
            $this->testInvalidImageXml
        );
    }

    public function testItIsALogMessage()
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testItIncludesTheExceptionMessageInTheStringReturnValue()
    {
        $expected = 'Error during processing catalog product image XML import callback: Test Message';
        $this->assertSame($expected, (string) $this->logMessage);
    }

    public function testItIncludesTheExceptionInTheContextArray()
    {
        $contextArray = $this->logMessage->getContext();
        $this->assertInternalType('array', $contextArray);
        $this->assertArrayHasKey('exception', $contextArray);
        $this->assertSame($this->testException, $contextArray['exception']);
    }

    public function testItIncludesTheProductImageXmlInTheContextArray()
    {
        $this->assertArrayHasKey('product_image_xml', $this->logMessage->getContext());
        $this->assertSame($this->testInvalidImageXml, $this->logMessage->getContext()['product_image_xml']);
    }
}
