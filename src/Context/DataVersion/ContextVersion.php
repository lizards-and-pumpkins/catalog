<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextVersion implements ContextPartBuilder
{
    const DATA_VERSION_REQUEST_PARAM = 'dataVersion';

    /**
     * @var DataVersion
     */
    private $defaultDataVersion;

    public function __construct(DataVersion $defaultDataVersion)
    {
        $this->defaultDataVersion = $defaultDataVersion;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet): string
    {
        if ($this->hasVersionInDataSet($inputDataSet)) {
            return $this->getVersionFromDataSet($inputDataSet);
        }

        if ($this->hasVersionRequestParam($inputDataSet)) {
            return (string) $this->getVersionRequestParamValue($inputDataSet);
        }

        return (string) $this->defaultDataVersion;
    }

    public function getCode(): string
    {
        return DataVersion::CONTEXT_CODE;
    }

    private function hasVersionInDataSet(array $inputDataSet): bool
    {
        return isset($inputDataSet[DataVersion::CONTEXT_CODE]);
    }

    private function getVersionFromDataSet(array $inputDataSet): string
    {
        return $inputDataSet[DataVersion::CONTEXT_CODE];
    }

    private function hasRequest(array $inputDataSet): bool
    {
        return isset($inputDataSet[ContextBuilder::REQUEST]);
    }

    private function getRequest(array $inputDataSet): HttpRequest
    {
        return $inputDataSet[ContextBuilder::REQUEST];
    }

    private function hasVersionRequestParam(array $inputDataSet): bool
    {
        return $this->hasRequest($inputDataSet) &&
               $this->getRequest($inputDataSet)->hasQueryParameter(self::DATA_VERSION_REQUEST_PARAM);
    }

    private function getVersionRequestParamValue(array $inputDataSet): ?string
    {
        return $this->getRequest($inputDataSet)->getQueryParameter(self::DATA_VERSION_REQUEST_PARAM);
    }
}
