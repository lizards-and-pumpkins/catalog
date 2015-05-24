<?php

namespace Brera;

class Snippet
{
    const KEY_PATTERN = '#^[a-zA-Z0-9:_\-]+$#';
    
    /**
     * @var string
     */
    private $key;
    
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $key
     * @param string $content
     * @return Snippet
     */
    public static function create($key, $content)
    {
        if (!is_string($key) || !preg_match(self::KEY_PATTERN, $key)) {
            throw new InvalidKeyException(sprintf('Key "%s" is invalid.', (is_scalar($key) ? $key : gettype($key))));
        }

        return new self($key, (string) $content);
    }

    /**
     * @param string $key
     * @param string $content
     */
    private function __construct($key, $content)
    {
        $this->key = $key;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
