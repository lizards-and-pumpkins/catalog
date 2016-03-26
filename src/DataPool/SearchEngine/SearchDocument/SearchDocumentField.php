<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidSearchDocumentFieldValueException;

class SearchDocumentField
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string[]
     */
    private $values;

    /**
     * @param string $key
     * @param string[] $values
     */
    private function __construct($key, array $values)
    {
        $this->key = $key;
        $this->values = $values;
    }

    /**
     * @param string $key
     * @param string[] $values
     * @return SearchDocumentField
     */
    public static function fromKeyAndValues($key, array $values)
    {
        self::validateKey($key);
        array_map([self::class, 'validateValue'], $values);

        return new self((string) $key, $values);
    }

    /**
     * @param string $value
     */
    public static function validateValue($value)
    {
        if (! is_scalar($value)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            $message = sprintf('Only string, integer, float and boolean attribute values are allowed, got "%s"', $type);
            throw new InvalidSearchDocumentFieldValueException($message);
        }
    }

    /**
     * @param mixed $key
     */
    private static function validateKey($key)
    {
        if (!is_string($key) || !strlen($key) || !ctype_alpha($key{0})) {
            throw new InvalidSearchDocumentFieldKeyException(
                'Search document field key must be a string led by a letter'
            );
        }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
