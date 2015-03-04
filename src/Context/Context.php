<?php

namespace Brera\Context;

interface Context
{
    /**
     * @return string
     */
    public function getId();
    
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
}
