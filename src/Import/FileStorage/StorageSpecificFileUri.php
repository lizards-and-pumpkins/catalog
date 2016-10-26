<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface StorageSpecificFileUri
{
    public function __toString() : string;
}
