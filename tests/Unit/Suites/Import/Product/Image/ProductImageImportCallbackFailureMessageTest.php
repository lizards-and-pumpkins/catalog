<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Logging\LogMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCallbackFailureMessage
 */
class ProductImageImportCallbackFailureMessageTest extends TestCase
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

    final protected function setUp(): void
    {
        $this->testException = new \Exception('Test Message');
        $this->logMessage = new ProductImageImportCallbackFailureMessage(
            $this->testException,
            $this->testInvalidImageXml
        );
    }

    public function testItIsALogMessage(): void
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testItIncludesTheExceptionMessageInTheStringReturnValue(): void
    {
        $expected = 'Error during processing catalog product image XML import callback: Test Message';
        $this->assertSame($expected, (string) $this->logMessage);
    }

    public function testItIncludesTheExceptionInTheContextArray(): void
    {
        $contextArray = $this->logMessage->getContext();

        $this->assertIsArray($contextArray);
        $this->assertArrayHasKey('exception', $contextArray);
        $this->assertSame($this->testException, $contextArray['exception']);
    }

    public function testItIncludesTheProductImageXmlInTheContextArray(): void
    {
        $this->assertArrayHasKey('product_image_xml', $this->logMessage->getContext());
        $this->assertSame($this->testInvalidImageXml, $this->logMessage->getContext()['product_image_xml']);
    }

    public function testTheContextSynopsisIncludesTheFileAndLine(): void
    {
        $synopsis = $this->logMessage->getContextSynopsis();
        $this->assertStringContainsString($this->testException->getFile(), $synopsis);
        $this->assertStringContainsString((string) $this->testException->getLine(), $synopsis);
        $this->assertStringContainsString($this->testInvalidImageXml, $synopsis);
    }
}
