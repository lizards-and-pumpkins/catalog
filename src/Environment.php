<?php

namespace Brera;

interface Environment
{
    /**
     * @param string $code
     * @return string
     */
	public function getValue($code);

    /**
     * @return string
     */
    public function getCode();
}
