<?php

namespace Brera\Product;

use Brera\DomainCommand;

/**
 * @covers \Brera\Product\ProjectProductStockQuantitySnippetDomainCommand
 */
class ProjectProductStockQuantitySnippetDomainCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectProductStockQuantitySnippetDomainCommand
     */
    private $domainCommand;

    /**
     * @var string
     */
    private $dummyPayloadString = 'foo';

    protected function setUp()
    {
        $this->domainCommand = new ProjectProductStockQuantitySnippetDomainCommand($this->dummyPayloadString);
    }

    public function testDomainCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainCommand::class, $this->domainCommand);
    }

    public function testPayloadStringIsReturned()
    {
        $result = $this->domainCommand->getPayload();
        $this->assertSame($this->dummyPayloadString, $result);
    }
}
