<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

class DefaultSelectedFiltersParser implements SelectedFiltersParser
{
    /**
     * @param string $filtersString
     * @return array[]
     */
    public function parse(string $filtersString): array
    {
        if ('' === trim($filtersString)) {
            return [];
        }

        $filters = $this->separateFilters($filtersString);

        return array_reduce($filters, function ($carry, string $filterString) {
            preg_match('/[^:]+:/', $filterString, $matches);

            list($key, $values) = explode(':', $filterString);

            return array_merge($carry, [$key => $this->getValues($values)]);
        }, []);
    }

    /**
     * @param string $value
     * @return string[]
     */
    private function getValues(string $value): array
    {
        if (preg_match('/^\[([^\[\]]+)\]$/', $value, $matches)) {
            return explode(',', $matches[1]);
        }

        return [$value];
    }

    /**
     * @param string $string
     * @return string[]
     */
    private function separateFilters(string $string) : array
    {
        $result = [];
        $start = 0;
        $nestingLevel = 0;

        for ($currentPosition = 0; $currentPosition < strlen($string); $currentPosition++) {
            if (',' === $string[$currentPosition] && 0 === $nestingLevel) {
                $result[] = substr($string, $start, $currentPosition - $start);
                $start = $currentPosition + 1;
            }

            if ('[' === $string[$currentPosition]) {
                $nestingLevel++;
            }

            if (']' === $string[$currentPosition]) {
                $nestingLevel--;
            }
        }

        $result[] = substr($string, $start, $currentPosition - $start);

        return $result;
    }
}
