<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileStorage
{
    public function getFileReference(StorageAgnosticFileUri $identifier) : File;

    public function contains(StorageAgnosticFileUri $identifier) : bool;

    public function putContent(StorageAgnosticFileUri $identifier, FileContent $content);

    public function getContent(StorageAgnosticFileUri $identifier) : FileContent;
}
