<?php

namespace LizardsAndPumpkins\Import\FileStorage;

interface StorageSpecificFileUri
{
    /**
     * @return string
     */
    public function __toString();
}
