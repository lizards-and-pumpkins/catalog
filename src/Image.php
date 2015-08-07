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
    public function getPath($size)
    {
        /* TODO: Re-implement w/o putting project specific data (size label, media dir) into general purpose class */

        return '/brera/' . self::MEDIA_DIR . '/' . $size . '/' . $this->src;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
