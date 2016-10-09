<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyException;

class Snippet
{
    const KEY_PATTERN = '#^[a-zA-Z0-9:_\-\./]+$#';
    
    /**
     * @var string
     */
    private $key;
    
    /**
     * @var string
     */
    private $content;

    public static function create(string $key, string $content) : Snippet
    {
        if (!preg_match(self::KEY_PATTERN, $key)) {
            throw new InvalidKeyException(sprintf('Key "%s" is invalid.', $key));
        }

        return new self($key, $content);
    }

    private function __construct(string $key, string $content)
    {
        $this->key = $key;
        $this->content = $content;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getContent() : string
    {
        return $this->content;
    }
}
