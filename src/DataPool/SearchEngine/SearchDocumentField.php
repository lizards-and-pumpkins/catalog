<?php

namespace Brera\DataPool\SearchEngine;

class SearchDocumentField
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $key
     * @param mixed $value
     * @throws InvalidSearchDocumentFieldKeyException
     */
    public function __construct($key, $value)
    {
        if (!is_string($key) || !strlen($key) || !ctype_alpha($key{0})) {
            throw new InvalidSearchDocumentFieldKeyException(
                'Search document filed key must be a string led by a letter'
            );
        }

        $this->key = (string) $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
