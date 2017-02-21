<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\ProcessingTimeTableDataBuilder;
use LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ReportEventProcessingTimeAverageConsoleCommand extends BaseCliCommand
{
    /**
     * @var ProcessingTimeTableDataBuilder
     */
    private $processingTimeTableDataBuilder;

    /**
     * @var LogfileReader
     */
    private $logFileReader;

    public function __construct(MasterFactory $masterFactory, CLImate $CLImate)
    {
        /** @var MasterFactory|ConsoleCommandFactory $masterFactory */
        $this->setCLImate($CLImate);
        $this->processingTimeTableDataBuilder = $masterFactory->createProcessingTimeTableDataBuilder();
        $this->logFileReader = $masterFactory->createDomainEventProcessingTimesLogFileReader();
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        $tableColumns = $this->processingTimeTableDataBuilder->getTableColumns();
        $sortDirections = $this->processingTimeTableDataBuilder->getSortDirections();

        return array_merge(
            parent::getCommandLineArgumentsArray($climate),
            [
                'sortBy'    => [
                    'prefix'       => 's',
                    'longPrefix'   => 'sortBy',
                    'description'  => 'Sort by field (' . implode('|', $tableColumns) . ')',
                    'defaultValue' => 'avg',
                ],
                'direction' => [
                    'prefix'       => 'd',
                    'longPrefix'   => 'direction',
                    'description'  => 'Sort direction (' . implode('|', $sortDirections) . ')',
                    'defaultValue' => 'asc',
                ],
                'logfile'   => [
                    'description' => 'Log file',
                    'required'    => true,
                ],
            ]
        );
    }

    protected function execute(CLImate $climate)
    {
        $eventHandlerProcessingTimes = $this->readEventHandlerProcessingTimes($this->getArg('logfile'));
        $tableData = $this->buildTableData($eventHandlerProcessingTimes);
        if (!$tableData) {
            $climate->yellow('No data to report');
        } else {
            $climate->table($tableData);
        }
    }

    /**
     * @param string $filePath
     * @return array[]
     */
    private function readEventHandlerProcessingTimes(string $filePath): array
    {
        return $this->logFileReader->getEventHandlerProcessingTimes($filePath);
    }

    /**
     * @param array[] $eventHandlerProcessingTimes
     * @return array[]
     */
    private function buildTableData(array $eventHandlerProcessingTimes): array
    {
        return $this->processingTimeTableDataBuilder
            ->buildSortedTableData($eventHandlerProcessingTimes, $this->getArg('sortBy'), $this->getArg('direction'));
    }

    protected function beforeExecute(CLImate $climate)
    {
        $this->validateLogFilePath($climate->arguments->get('logfile'));
        $this->validateSortField($climate->arguments->get('sortBy'));
        $this->validateSortDirection($climate->arguments->get('direction'));
    }

    private function validateLogFilePath(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Log file not found: "%s"', $filePath));
        }
        if (!is_readable($filePath)) {
            throw new \RuntimeException(sprintf('Log file not readable: "%s"', $filePath));
        }
    }

    private function validateSortField(string $field)
    {
        if (!in_array($field, $this->processingTimeTableDataBuilder->getTableColumns())) {
            throw new \RuntimeException(sprintf('Invalid sort field: "%s"', $field));
        }
    }

    private function validateSortDirection(string $direction)
    {
        if (!in_array($direction, $this->processingTimeTableDataBuilder->getSortDirections())) {
            throw new \RuntimeException(sprintf('Invalid sort direction: "%s"', $direction));
        }
    }
}
