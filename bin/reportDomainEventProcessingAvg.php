#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\BaseCliCommand;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class CalculateAverageDomainEventProcessingTime extends BaseCliCommand
{
    public function __construct()
    {
        $this->setCLImate(new CLImate());
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'sortBy' => [
                'prefix' => 's',
                'longPrefix' => 'sortBy',
                'description' => 'Sort by field (handler|count|total|avg)',
                'defaultValue' => 'avg',
            ],
            'direction' => [
                'prefix' => 'd',
                'longPrefix' => 'direction',
                'description' => 'Sort direction (asc|desc)',
                'defaultValue' => 'asc',
            ],
            'logfile' => [
                'description' => 'Log file',
                'required' => true
            ]
        ]);
    }

    protected function execute(CLImate $climate)
    {
        $filePath = $this->getArg('logfile');
        $tableData = $this->sortTableData(
            $this->collectTableDataFromFile($filePath),
            $this->getArg('sortBy'),
            $this->getArg('direction')
        );
        if (!$tableData) {
            $climate->yellow('No data to report');
        } else {
            $climate->table($tableData);
        }
    }

    /**
     * @param array[] $tableData
     * @param string $field
     * @param string $direction
     * @return array[]
     */
    private function sortTableData($tableData, $field, $direction)
    {
        $directionalOperator = $direction === 'asc' ? 1 : -1;
        usort($tableData, function (array $rowA, array $rowB) use ($field, $directionalOperator) {
            $valueA = $this->getComparisonValueFromRow($rowA, $field);
            $valueB = $this->getComparisonValueFromRow($rowB, $field);
            $result = $this->threeWayCompare($valueA, $valueB);
            return $result * $directionalOperator;
        });
        return $tableData;
    }

    /**
     * @param mixed $valueA
     * @param mixed $valueB
     * @return int
     */
    private function threeWayCompare($valueA, $valueB)
    {
        if ($valueA > $valueB) {
            $result = 1;
        } elseif ($valueA < $valueB) {
            $result = -1;
        } else {
            $result = 0;
        }
        return $result;
    }

    /**
     * @param mixed[] $row
     * @param string $field
     * @return mixed
     */
    private function getComparisonValueFromRow(array $row, $field)
    {
        $key = $this->getArrayKeyFromSortByField($field);
        return $row[$key];
    }

    /**
     * @param string $field
     * @return string
     */
    private function getArrayKeyFromSortByField($field)
    {
        return array_search($field, [
            'Handler' => 'handler',
            'Count' => 'count',
            'Total Sec' => 'total',
            'Average Sec' => 'avg'
        ]);
    }

    /**
     * @param string $filePath
     * @return array[]
     */
    private function collectTableDataFromFile($filePath)
    {
        $eventHandlerStats = $this->readEventHandlerStatsFromFile($filePath);
        return $this->buildTableDataFromStats($eventHandlerStats);
    }

    /**
     * @param string $filePath
     * @return array[]
     */
    private function readEventHandlerStatsFromFile($filePath)
    {
        $eventHandlers = [];
        foreach ($this->getDomainEventHandlerRecordsFromFile($filePath) as $record) {
            list($domainEventHandler, $time) = $record;
            $eventHandlers[$domainEventHandler][] = $time;
        }
        return $eventHandlers;
    }

    /**
     * @param string $filePath
     * @return \Generator
     */
    private function getDomainEventHandlerRecordsFromFile($filePath)
    {
        $f = fopen($filePath, 'r');
        $matches = null;
        while (!feof($f)) {
            if (preg_match("/^.{25}\tDomainEventHandler::process (\\S+) (\\S+)/", fgets($f), $matches)) {
                yield array_slice($matches, 1);
            }
        }
        fclose($f);
    }

    /**
     * @param array[] $eventHandlerStats
     * @return array[]
     */
    private function buildTableDataFromStats(array $eventHandlerStats)
    {
        return array_map(function ($handler) use ($eventHandlerStats) {
            $count = count($eventHandlerStats[$handler]);
            $sum = array_sum($eventHandlerStats[$handler]);
            return $this->getTableRow($handler, $count, $sum);
        }, array_keys($eventHandlerStats));
    }

    /**
     * @param string $handler
     * @param int $count
     * @param float $sum
     * @return mixed[]
     */
    private function getTableRow($handler, $count, $sum)
    {
        return [
            'Handler' => $handler,
            'Count' => $count,
            'Total Sec' => sprintf('%11.4F', $sum),
            'Average Sec' => sprintf('%.4F', $sum / $count)
        ];
    }

    protected function beforeExecute(CLImate $climate)
    {
        $this->validateLogFilePath($climate->arguments->get('logfile'));
        $this->validateSortField($climate->arguments->get('sortBy'));
        $this->validateSortDirection($climate->arguments->get('direction'));
    }

    /**
     * @param string $filePath
     */
    private function validateLogFilePath($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Log file not found: "%s"', $filePath));
        }
        if (!is_readable($filePath)) {
            throw new \RuntimeException(sprintf('Log file not readable: "%s"', $filePath));
        }
    }

    /**
     * @param string $order
     */
    private function validateSortField($order)
    {
        if (!in_array($order, ['handler', 'count', 'total', 'avg'])) {
            throw new \RuntimeException(sprintf('Invalid order: "%s"', $order));
        }
    }

    /**
     * @param string $direction
     */
    private function validateSortDirection($direction)
    {
        if (!in_array($direction, ['asc', 'desc'])) {
            throw new \RuntimeException(sprintf('Invalid sort direction: "%s"', $direction));
        }
    }
}

(new CalculateAverageDomainEventProcessingTime())->run();
