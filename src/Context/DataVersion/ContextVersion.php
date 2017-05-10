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
    private $dataVersion;

    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet): string
    {
        if ($this->hasVersion($inputDataSet)) {
            return (string) $this->getVersion($inputDataSet);
        }
        if ($this->hasVersionRequestParam($inputDataSet)) {
            return (string) $this->getVersionRequestParamValue($inputDataSet);
        }
        return (string) $this->dataVersion;
    }

    public function getCode(): string
    {
        return DataVersion::CONTEXT_CODE;
    }

    private function hasVersion(array $inputDataSet): bool
    {
        return isset($inputDataSet[DataVersion::CONTEXT_CODE]);
    }

    private function getVersion(array $inputDataSet)
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

    private function getVersionRequestParamValue(array $inputDataSet)
    {
        return $this->getRequest($inputDataSet)->getQueryParameter(self::DATA_VERSION_REQUEST_PARAM);
    }
}
