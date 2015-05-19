<?php

namespace Brera;

class Image
{
    const MEDIA_DIR = 'media/product';

    /**
     * @var string
     */
    private $src;

    /**
     * @var string
     */
    private $label;

    /**
     * @param string $src
     * @param string $label
     */
    public function __construct($src, $label = '')
    {
        $this->src = $src;
        $this->label = $label;
    }

    /**
     * @param string $size
     * @return string
     */
    public function getSrc($size)
    {
        /* TODO: Re-implement without hard-coding project specific image size label into general purpose class */

        return self::MEDIA_DIR . '/' . $size . '/' . $this->src;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
