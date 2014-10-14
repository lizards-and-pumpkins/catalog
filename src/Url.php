<?php


namespace Brera\PoC;


abstract class Url
{
    /**
     * @var string
     */
    private $url;

    /**
     * @param string $url
     */
    protected function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->url;
    }

    /**
     * @param string $url
     * @return HttpUrl
     */
    public static function fromString($url)
    {
        if (strtolower(substr($url, 0, 5)) === 'https') {
            return new HttpsUrl($url);
        } else {
            return new HttpUrl($url);
        }
    }

    /**
     * @return bool
     */
    public function isProtocolEncrypted()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return parse_url($this->url, \PHP_URL_PATH);
    }
} 