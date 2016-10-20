<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class ContextVersion implements ContextPartBuilder
{
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
    public function getValue(array $inputDataSet) : string
    {
        return (string) ($inputDataSet[DataVersion::CONTEXT_CODE] ?? $this->dataVersion);
    }

    public function getCode() : string
    {
        return DataVersion::CONTEXT_CODE;
    }
}
