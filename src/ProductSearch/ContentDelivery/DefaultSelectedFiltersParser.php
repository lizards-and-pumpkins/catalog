<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\MalformedSelectedFiltersQueryStringException;

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

        return array_reduce($filters, function ($carry, string $filterString) use ($filtersString) {
            $this->validateFilterString($filterString, $filtersString);

            list($key, $valuesString) = explode(':', $filterString);

            $values = $this->getValues($valuesString);
            $this->validateValues($filtersString, ...$values);

            return array_merge($carry, [$key => $values]);
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

    private function validateFilterString(string $filterString, string $filtersString): void
    {
        if (! preg_match('/^[^:]+:\[?[^\[\]]+\]?$/', $filterString)) {
            $this->throwMalformedFiltersStringException($filtersString);
        }
    }

    private function validateValues(string $filtersString, string ...$values): void
    {
        every($values, function (string $value) use ($filtersString) {
            if ('' === trim($value)) {
                $this->throwMalformedFiltersStringException($filtersString);
            }
        });
    }

    private function throwMalformedFiltersStringException(string $filtersString): void
    {
        throw new MalformedSelectedFiltersQueryStringException(
            sprintf('Selected filters query string %s is malformed.', $filtersString)
        );
    }
}
