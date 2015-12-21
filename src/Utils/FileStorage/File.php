<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

interface File
{
    /**
     * @return bool
     */
    public function exists();

    /**
     * @return FileContent
     */
    public function getContent();

    /**
     * @return StorageSpecificFileUri
     */
    public function getInStorageUri();

    /**
     * @return string
     */
    public function __toString();
}
