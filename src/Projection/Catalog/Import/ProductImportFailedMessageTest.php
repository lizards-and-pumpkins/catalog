<?php

namespace Brera\Projection\Catalog\Import;

use Brera\Log\LogMessage;
use Brera\Product\ProductId;

/**
 * @covers \Brera\Projection\Catalog\Import\ProductImportFailedMessage
 */
class ProductImportFailedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var string
     */
    private $dummyFailureExceptionMessage = 'bar';

    /**
     * @var ProductImportFailedMessage
     */
    private $logMessage;

    protected function setUp()
    {
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductId->method('__toString')->willReturn('foo');

        $exception = new \Exception($this->dummyFailureExceptionMessage);

        $this->logMessage = new ProductImportFailedMessage($this->stubProductId, $exception);
    }

    public function testLogMessageInterfaceIsImplemented()
    {
        $this->assertInstanceOf(LogMessage::class, $this->logMessage);
    }

    public function testProductIdIsReturnedAsContext()
    {
        $result = $this->logMessage->getContext();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testLogMessageIncludesProductId()
    {
        $this->assertContains((string) $this->stubProductId, (string) $this->logMessage);
    }

    public function testLogMessageIncludesFailureExceptionMessage()
    {
        $this->assertContains($this->dummyFailureExceptionMessage, (string) $this->logMessage);
    }
}
