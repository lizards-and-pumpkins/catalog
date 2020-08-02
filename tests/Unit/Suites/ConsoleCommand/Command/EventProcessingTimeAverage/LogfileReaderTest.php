<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage;

use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader
 */
class LogfileReaderTest extends TestCase
{
    use TestFileFixtureTrait;

    public function testParsesDomainEventHandlerProcessingTimesFromLogfile(): void
    {
        $testLogData = <<<EOT
2004-02-12T15:19:21+00:00\tDomainEventHandler::process FooEventHandler 2.0
2004-02-12T15:19:21+00:00\tDomainEventHandler::process FooEventHandler 1.6
2004-02-12T15:19:21+00:00\tDomainEventHandler::process FooEventHandler 3.0
2004-02-12T15:19:21+00:00\tSomethingFoo Bar Baz Qux 3.0
2004-02-12T15:19:21+00:00\tDomainEventHandler::process BarEventHandler 1.0
2004-02-12T15:19:21+00:00\tDomainEventHandler::process BarEventHandler 2.0
2004-02-12T15:19:21+00:00\tDomainEventHandler::process BarEventHandler 1.0
EOT;
        $expected = [
            'FooEventHandler' => ["2.0", "1.6", "3.0"],
            'BarEventHandler' => ["1.0", "2.0", "1.0"],
        ];
        
        $testFile = $this->getUniqueTempDir() . 'test-processing-times.log';
        $this->createFixtureFile($testFile, $testLogData);

        $tableData = (new LogfileReader())->getEventHandlerProcessingTimes($testFile);
        $this->assertSame($expected, $tableData);
    }
}
