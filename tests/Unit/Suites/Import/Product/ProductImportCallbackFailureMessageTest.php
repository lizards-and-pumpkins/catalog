<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Logging\LogMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ProductImportCallbackFailureMessageTest extends TestCase
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

    final protected function setUp(): void
    {
        $this->testException = new \Exception('Test Message');
        $this->logMessage = new ProductImportCallbackFailureMessage($this->testException, $this->testInvalidXml);
    }
    
    public function testItImplementsLogMessage(): void
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testItReturnsTheExceptionMessage(): void
    {
        $expected = 'Error during processing catalog product XML import for product "- unknown -": Test Message';
        $this->assertSame($expected, (string) $this->logMessage);
    }

    public function testItIncludesTheProductXmlInTheContextArray(): void
    {
        $this->assertIsArray($this->logMessage->getContext());
        $this->assertArrayHasKey('product_xml', $this->logMessage->getContext());
        $this->assertSame($this->testInvalidXml, $this->logMessage->getContext()['product_xml']);
    }

    public function testItIncludesTheExceptionInTheContextArray(): void
    {
        $this->assertArrayHasKey('exception', $this->logMessage->getContext());
        $this->assertSame($this->testException, $this->logMessage->getContext()['exception']);
    }

    public function testItExtractsTheProductSkuFromTheXmlFragment(): void
    {
        $productXml = '<product sku="test-id"></product>';
        $logMessage = new ProductImportCallbackFailureMessage($this->testException, $productXml);
        $expected = 'Error during processing catalog product XML import for product "test-id": Test Message';
        $this->assertSame($expected, (string) $logMessage);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis(): void
    {
        $synopsis = $this->logMessage->getContextSynopsis();
        $this->assertStringContainsString($this->testException->getFile(), $synopsis);
        $this->assertStringContainsString((string) $this->testException->getLine(), $synopsis);
    }
}
