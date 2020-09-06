<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Logging\LogMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage
 */
class CatalogListingImportCallbackFailureMessageTest extends TestCase
{
    /**
     * @var CatalogListingImportCallbackFailureMessage
     */
    private $logMessage;

    /**
     * @var \Exception
     */
    private $testException;
    
    private $testListingXml = '<listing/>';

    final protected function setUp(): void
    {
        $this->testException = new \Exception('Test Message');
        $this->logMessage = new CatalogListingImportCallbackFailureMessage(
            $this->testException,
            $this->testListingXml
        );
    }

    public function testItIsALogMessage(): void
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testItIncludesTheExceptionMessageInTheStringReturnValue(): void
    {
        $this->assertSame(
            'An error occurred while processing catalog XML import listing callbacks: Test Message',
            (string) $this->logMessage
        );
    }

    public function testItIncludesTheExceptionInTheContextArray(): void
    {
        $contextArray = $this->logMessage->getContext();
        $this->assertIsArray($contextArray);
        $this->assertArrayHasKey('exception', $contextArray);
        $this->assertSame($this->testException, $contextArray['exception']);
    }

    public function testItIncludesTheListingXmlInTheContextArray(): void
    {
        $contextArray = $this->logMessage->getContext();
        $this->assertArrayHasKey('listing_xml', $contextArray);
        $this->assertSame($this->testListingXml, $contextArray['listing_xml']);
    }

    public function testTheContextSynopsisIncludesTheFileAndLine(): void
    {
        $synopsis = $this->logMessage->getContextSynopsis();
        $this->assertStringContainsString($this->testException->getFile(), $synopsis);
        $this->assertStringContainsString((string) $this->testException->getLine(), $synopsis);
    }
}
