<?php

namespace Brera\Product;

use Brera\DomainCommand;

/**
 * @covers \Brera\Product\ProjectProductQuantitySnippetDomainCommand
 */
class ProjectProductQuantitySnippetDomainCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectProductQuantitySnippetDomainCommand
     */
    private $domainCommand;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var int
     */
    private $quantity;

    protected function setUp()
    {
        $this->productSku = 'foo';
        $this->quantity = 1;
        $this->domainCommand = ProjectProductQuantitySnippetDomainCommand::create($this->productSku, $this->quantity);
    }

    public function testDomainCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainCommand::class, $this->domainCommand);
    }

    public function testExceptionIsThrownIfNonStringSkuIsPassedToConstructor()
    {
        $productSku = 1;
        $quantity = 1;
        $this->setExpectedException(\InvalidArgumentException::class, 'Product SKU is supposed to be a string.');
        ProjectProductQuantitySnippetDomainCommand::create($productSku, $quantity);
    }

    public function testExceptionIsThrownIfNonIntegerQuantityIsPassedToConstructor()
    {
        $productSku = 'foo';
        $quantity = 'bar';
        $this->setExpectedException(\InvalidArgumentException::class, 'Product quantity is supposed to be an integer.');
        ProjectProductQuantitySnippetDomainCommand::create($productSku, $quantity);
    }

    public function testProductSkuIsReturned()
    {
        $result = $this->domainCommand->getSku();
        $this->assertSame($this->productSku, $result);
    }

    public function testProductQuantityIsReturned()
    {
        $result = $this->domainCommand->getQuantity();
        $this->assertSame($this->quantity, $result);
    }
}
