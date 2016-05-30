<?php

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
    public function getValue(array $inputDataSet)
    {
        return isset($inputDataSet[DataVersion::CONTEXT_CODE]) ?
            (string) $inputDataSet[DataVersion::CONTEXT_CODE] :
            (string) $this->dataVersion;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return DataVersion::CONTEXT_CODE;
    }
}
