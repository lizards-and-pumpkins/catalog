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
     *
     * @return Url
     * @throws UnknownProtocolException
     */
    public static function fromString($url)
    {
        $protocol = parse_url($url, PHP_URL_SCHEME);
        switch ($protocol) {
            case 'https':
                return new HttpsUrl($url);
            case 'http':
                return new HttpUrl($url);
            default:
                throw new UnknownProtocolException();
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
