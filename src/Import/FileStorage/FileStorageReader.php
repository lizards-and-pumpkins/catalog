<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileStorageReader
{
    public function getFileContents(string $filePath): string;
}
