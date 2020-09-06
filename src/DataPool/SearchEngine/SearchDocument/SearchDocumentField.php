<?php

declare(strict_types=1);

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
    private function __construct(string $key, array $values)
    {
        $this->key = $key;
        $this->values = $values;
    }

    /**
     * @param string $key
     * @param string[] $values
     * @return SearchDocumentField
     */
    public static function fromKeyAndValues(string $key, array $values) : SearchDocumentField
    {
        self::validateKey($key);
        every($values, [self::class, 'validateValue']);

        return new self((string) $key, $values);
    }

    /**
     * @param string|int|float|bool $value
     */
    public static function validateValue($value): void
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
    private static function validateKey(string $key): void
    {
        if (!strlen($key) || !ctype_alpha($key{0})) {
            throw new InvalidSearchDocumentFieldKeyException('Search document field key must be led by a letter.');
        }
    }

    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @return string[]
     */
    public function getValues() : array
    {
        return $this->values;
    }
}
