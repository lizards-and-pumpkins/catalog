<?php

namespace Brera;

class Image
{
    const MEDIA_DIR = 'media';

    /**
     * @var string
     */
    private $src;

    /**
     * @var string
     */
    private $label;

    public function __construct($src, $label = '')
    {
        $this->src = self::MEDIA_DIR . DIRECTORY_SEPARATOR . $src;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
