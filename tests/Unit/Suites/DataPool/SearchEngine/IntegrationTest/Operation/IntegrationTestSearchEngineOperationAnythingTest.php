<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationAnything
 */
class IntegrationTestSearchEngineOperationAnythingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestSearchEngineOperationAnything
     */
    private $operation;

    final protected function setUp()
    {
        $this->operation = new IntegrationTestSearchEngineOperationAnything();
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface()
    {
        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $this->operation);
    }

    public function testMatchesAnyDocument()
    {
        $this->assertTrue($this->operation->matches($this->createMock(SearchDocument::class)));
    }
}
