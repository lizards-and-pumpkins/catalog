<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\DataVersion;

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
