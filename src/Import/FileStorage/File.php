<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface File
{
    public function exists() : bool;

    public function getContent() : FileContent;

    public function getInStorageUri() : StorageSpecificFileUri;

    public function __toString() : string;
}
