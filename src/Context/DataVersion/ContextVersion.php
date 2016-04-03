<?php

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class ContextVersion implements ContextPartBuilder
{
    const CODE = 'version';
    
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
     * @param string[] $otherContextParts
     * @return string
     */
    public function getValue(array $inputDataSet, array $otherContextParts)
    {
        return isset($inputDataSet[self::CODE]) ?
            (string) $inputDataSet[self::CODE] :
            (string) $this->dataVersion;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }
}
