<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperationAnything
 */
class IntegrationTestSearchEngineOperationAnythingTest extends TestCase
{
    /**
     * @var IntegrationTestSearchEngineOperationAnything
     */
    private $operation;

    final protected function setUp(): void
    {
        $this->operation = new IntegrationTestSearchEngineOperationAnything();
    }

    public function testImplementsIntegrationTestSearchEngineOperationInterface(): void
    {
        $this->assertInstanceOf(IntegrationTestSearchEngineOperation::class, $this->operation);
    }

    public function testMatchesAnyDocument(): void
    {
        /** @var SearchDocument|MockObject $stubSearchDocument */
        $stubSearchDocument = $this->createMock(SearchDocument::class);

        $this->assertTrue($this->operation->matches($stubSearchDocument));
    }
}
