<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage;

class LogfileReader
{

    public function getEventHandlerProcessingTimes(string $logFilePath): array
    {
        $eventHandlers = [];
        foreach ($this->readDomainEventHandlerProcessingTimes($logFilePath) as $record) {
            list($domainEventHandler, $time) = $record;
            $eventHandlers[$domainEventHandler][] = $time;
        }

        return $eventHandlers;
    }

    private function readDomainEventHandlerProcessingTimes($logFilePath)
    {
        $f = fopen($logFilePath, 'r');
        $matches = null;
        while (!feof($f)) {
            $pattern = "/^.{25}\tDomainEventHandler::process (?<domainEventHandler>\\S+) (?<time>\\S+)/";
            $fgets = (string) fgets($f);
            if (preg_match($pattern, $fgets, $matches)) {
                yield [$matches['domainEventHandler'], $matches['time']];
            }
        }
        fclose($f);
    }
}
