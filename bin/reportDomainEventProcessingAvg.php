#!/usr/bin/env php
<?php

namespace Brera;

use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';

class CalculateAverageDomainEventProcessingTime
{
    public function run()
    {
        $climate = new CLImate();
        try {
            $this->prepareCommandLineArguments($climate);
            $this->execute($climate);
        } catch (\Exception $e) {
            $climate->error($e->getMessage());
            $climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
            $climate->usage();
        }
    }

    private function prepareCommandLineArguments(CLImate $climate)
    {
        $climate->arguments->add([
            'logfile' => [
                'description' => 'Log file',
            ]
        ]);

        $climate->arguments->parse();
    }

    private function execute(CLImate $climate)
    {
        $filePath = $climate->arguments->get('logfile');
        $this->validateLogfilePath($filePath);
        $tableData = $this->collectTableDataFromFile($filePath);
        $climate->table($tableData);
    }

    /**
     * @param string $filePath
     */
    private function validateLogfilePath($filePath)
    {
        if (empty($filePath)) {
            throw new \RuntimeException('No log file specified');
        }
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Log file not found: "%s"', $filePath));
        }
        if (!is_readable($filePath)) {
            throw new \RuntimeException(sprintf('Log file not readable: "%s"', $filePath));
        }
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
}

(new CalculateAverageDomainEventProcessingTime())->run();
