<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\MalformedCriteriaQueryStringException;

class DefaultCriteriaParser implements CriteriaParser
{
    public function createCriteriaFromString(string $criteriaString): SearchCriteria
    {
        if ('' === trim($criteriaString)) {
            return new SearchCriterionAnything();
        }

        try {
            return $this->parseCriteriaString($criteriaString);
        } catch (MalformedCriteriaQueryStringException $exception) {
            throw new MalformedCriteriaQueryStringException(
                sprintf('Criteria query string %s is malformed.', $criteriaString)
            );
        }
    }

    private function parseCriteriaString(string $criteriaString): SearchCriteria
    {
        if (preg_match('/^(?P<condition>and|or):\[(?P<subCriteria>.+,.+)\]$/', $criteriaString, $matches)) {
            $subCriteriaStrings = $this->explodeCriteriaString($matches['subCriteria']);
            $subCriteria = array_map(function (string $subCriteriaString): SearchCriteria {
                return $this->parseCriteriaString($subCriteriaString);
            }, $subCriteriaStrings);

            return CompositeSearchCriterion::create($matches['condition'], ...$subCriteria);
        }

        return $this->parseSingleCriteria($criteriaString);
    }

    private function parseSingleCriteria(string $criteriaString): SearchCriteria
    {
        $this->validateCriteriaString($criteriaString);

        list($fieldName, $valuesString) = explode(':', $criteriaString, 2);

        if (preg_match('/^\{(?P<condition>and|or):\[(?P<values>.+,.+)\]\}$/', $valuesString, $matches)) {
            $values = explode(',', $matches['values']);
            $subCriteria = array_map(function (string $value) use ($fieldName) {
                return new SearchCriterionEqual($fieldName, $value);
            }, $values);

            return CompositeSearchCriterion::create($matches['condition'], ...$subCriteria);
        }

        $this->validateSingleValue($valuesString);

        return new SearchCriterionEqual($fieldName, $valuesString);
    }

    /**
     * @param string $string
     * @return string[]
     */
    private function explodeCriteriaString(string $string): array
    {
        $result = [];
        $start = 0;
        $nestingLevel = 0;

        for ($currentPosition = 0; $currentPosition < strlen($string); $currentPosition++) {
            if (',' === $string[$currentPosition] && 0 === $nestingLevel) {
                $result[] = substr($string, $start, $currentPosition - $start);
                $start = $currentPosition + 1;
            }

            if ('{' === $string[$currentPosition]) {
                $nestingLevel++;
            }

            if ('}' === $string[$currentPosition]) {
                $nestingLevel--;
            }
        }

        $result[] = substr($string, $start, $currentPosition - $start);

        return $result;
    }

    private function validateCriteriaString(string $criteriaString)
    {
        if (! preg_match('/^.+:.+$/', $criteriaString)) {
            throw new MalformedCriteriaQueryStringException(
                sprintf('Criteria query string %s is malformed.', $criteriaString)
            );
        }
    }

    private function validateSingleValue(string $valuesString)
    {
        if (! preg_match('/^[^\[\]\{\}: ,]+$/', $valuesString)) {
            throw new MalformedCriteriaQueryStringException(
                sprintf('Criteria value string %s is malformed.', $valuesString)
            );
        }
    }
}
