<?php

namespace Brera\Environment;

interface Environment
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
}
