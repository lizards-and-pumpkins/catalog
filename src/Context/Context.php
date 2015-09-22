<?php

namespace LizardsAndPumpkins\Context;

interface Context extends \JsonSerializable
{
    /**
     * @return string
     */
    public function toString();

    /**
     * @param string[] $requestedParts
     * @return string
     */
    public function getIdForParts(array $requestedParts);
    
    /**
     * @param string $code
     * @return string
     */
    public function getValue($code);

    /**
     * @return string[]
     */
    public function getSupportedCodes();

    /**
     * @param string $code
     * @return bool
     */
    public function supportsCode($code);

    /**
     * @param Context $otherContext
     * @return bool
     */
    public function isSubsetOf(Context $otherContext);
}
