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
     * @param string $value
     */
    private function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return SearchDocumentField
     * @throws InvalidSearchDocumentFieldKeyException
     */
    public static function fromKeyAndValue($key, $value)
    {
        if (!is_string($key) || !strlen($key) || !ctype_alpha($key{0})) {
            throw new InvalidSearchDocumentFieldKeyException(
                'Search document filed key must be a string led by a letter'
            );
        }

        return new self((string) $key, $value);
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
