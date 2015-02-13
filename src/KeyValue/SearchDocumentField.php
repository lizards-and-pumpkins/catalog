<?php

namespace Brera\KeyValue;

class SearchDocumentField
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value)
    {
        if (!is_string($key) || !strlen($key) || !ctype_alpha($key{0})) {
            throw new InvalidSearchDocumentFieldKeyException(
                'Search document filed key must be a string led by a letter'
            );
        }

        $this->key = (string) $key;
        $this->value = (string) $value;
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
    public function getValue()
    {
        return $this->value;
    }
}
