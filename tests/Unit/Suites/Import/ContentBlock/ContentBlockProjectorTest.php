<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Projector;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 */
class ContentBlockProjectorTest extends TestCase
{
    /**
     * @var Projector|MockObject
     */
    private $mockSnippetProjector;

    /**
     * @var ContentBlockProjector
     */
    private $projector;

    final protected function setUp(): void
    {
        $this->mockSnippetProjector = $this->createMock(Projector::class);
        $this->projector = new ContentBlockProjector($this->mockSnippetProjector);
    }

    public function testImplementsProjectorInterface(): void
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testThrowsAnExceptionIfProjectionSourceDataIsNotContentBlockSource(): void
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project($projectionSourceData = 'foo');
    }

    public function testSnippetIsWrittenIntoDataPool(): void
    {
        /** @var ContentBlockSource|MockObject $dummyContentBlockSource */
        $dummyContentBlockSource = $this->createMock(ContentBlockSource::class);
        $this->mockSnippetProjector->expects($this->once())->method('project')->with($dummyContentBlockSource);

        $this->projector->project($dummyContentBlockSource);
    }
}
