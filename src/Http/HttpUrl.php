<?php


namespace Brera\PoC;


class HttpUrl
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
     * @param string $url
     * @return string
     * @throws \InvalidArgumentException
     */
    private static function detectProtocol($url)
    {
        $protocol = strstr($url, ':', true);
        if (false === $protocol) {
            throw new \InvalidArgumentException(sprintf('No protocol detected in URL "%s"', $url));
        }
        return $protocol;
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
     * @return HttpUrl
     * @throws UnknownProtocolException
     */
    public static function fromString($url)
    {
        $protocol = self::detectProtocol($url);
        switch ($protocol) {
            case 'https':
                return new HttpsUrl($url);
            case 'http':
                return new HttpUrl($url);
            default:
                throw new UnknownProtocolException(sprintf('Protocol can not be handled "%s"', $protocol));
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
        $path = parse_url($this->url, \PHP_URL_PATH);
        if (null === $path) {
            return '/';
        }
        
        return $path;
    }
} 
