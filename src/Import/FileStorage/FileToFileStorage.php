<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileToFileStorage
{
    public function isPresent(File $file) : bool;

    public function read(File $file) : string;

    public function write(File $file);
}
