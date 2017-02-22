<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage;

class ProcessingTimeTableDataBuilder
{
    private static $tableColumns = [
        'Handler'     => 'handler',
        'Count'       => 'count',
        'Total Sec'   => 'total',
        'Average Sec' => 'avg',
    ];

    private static $sortDirections = [
        -1 => 'asc',
        1  => 'desc',
    ];

    /**
     * @param array[] $eventHandlerProcessingTimes
     * @param string $sortBy
     * @param string $direction
     * @return array[]
     */
    public function buildSortedTableData(array $eventHandlerProcessingTimes, string $sortBy, string $direction): array
    {
        return $this->sortTableData($this->buildTableData($eventHandlerProcessingTimes), $sortBy, $direction);
    }

    /**
     * @param array[] $eventHandlerProcessingTimes
     * @return array[]
     */
    private function buildTableData(array $eventHandlerProcessingTimes): array
    {
        return array_map(function ($handler) use ($eventHandlerProcessingTimes) {
            $count = count($eventHandlerProcessingTimes[$handler]);
            $sum = array_sum($eventHandlerProcessingTimes[$handler]);

            return $this->getTableRow($handler, $count, $sum);
        }, array_keys($eventHandlerProcessingTimes));
    }

    /**
     * @param string $handler
     * @param int $count
     * @param float|int $sum
     * @return mixed[]
     */
    private function getTableRow(string $handler, int $count, $sum): array
    {
        return [
            'Handler'     => $handler,
            'Count'       => $count,
            'Total Sec'   => sprintf('%11.4F', $sum),
            'Average Sec' => sprintf('%.4F', $sum / $count),
        ];
    }

    /**
     * @param array[] $tableData
     * @param string $field
     * @param string $direction
     * @return array[]
     */
    private function sortTableData(array $tableData, string $field, string $direction): array
    {
        $directionalFactor = array_flip(self::$sortDirections)[$direction];
        usort(
            $tableData,
            function (array $rowA, array $rowB) use ($field, $directionalFactor) {
                $valueA = $this->getComparisonValueFromRow($rowA, $field);
                $valueB = $this->getComparisonValueFromRow($rowB, $field);
                $result = $valueA <=> $valueB;

                return $result * $directionalFactor;
            }
        );

        return $tableData;
    }

    /**
     * @param mixed[] $row
     * @param string $field
     * @return mixed
     */
    private function getComparisonValueFromRow(array $row, string $field)
    {
        $key = $this->getArrayKeyFromSortByField($field);

        return $row[$key];
    }

    private function getArrayKeyFromSortByField(string $field): string
    {
        return array_search($field, self::$tableColumns);
    }

    /**
     * @return string[]
     */
    public function getTableColumns(): array
    {
        return array_values(self::$tableColumns);
    }

    /**
     * @return string[]
     */
    public function getSortDirections(): array
    {
        return array_values(self::$sortDirections);
    }
}
