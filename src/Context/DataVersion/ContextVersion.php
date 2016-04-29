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
     * @return string
     */
    public function getValue(array $inputDataSet)
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
