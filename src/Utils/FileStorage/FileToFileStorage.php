<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

interface FileToFileStorage
{
    /**
     * @param File $file
     * @return bool
     */
    public function isPresent(File $file);

    /**
     * @param File $file
     * @return string
     */
    public function read(File $file);

    /**
     * @param File $file
     * @return void
     */
    public function write(File $file);
}
