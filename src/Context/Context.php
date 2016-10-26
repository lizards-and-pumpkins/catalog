<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context;

interface Context extends \JsonSerializable
{
    public function __toString() : string;

    public function getIdForParts(string ...$requestedParts) : string;
    
    public function getValue(string $code) : string;

    /**
     * @return string[]
     */
    public function getSupportedCodes() : array;

    public function supportsCode(string $code) : bool;

    public function isSubsetOf(Context $otherContext) : bool;

    public function contains(Context $otherContext) : bool;

    /**
     * @param string[] $dataSet
     * @return bool
     */
    public function matchesDataSet(array $dataSet) : bool;
}
