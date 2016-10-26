<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileStorageWriter
{
    public function putFileContents(string $filePath, string $contents);
}
