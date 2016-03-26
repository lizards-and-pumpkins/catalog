<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\InvalidContentBlockIdException;

class ContentBlockId
{
    /**
     * @var string
     */
    private $contentBlockIdString;

    /**
     * @param string $contentBlockIdString
     */
    private function __construct($contentBlockIdString)
    {
        $this->contentBlockIdString = $contentBlockIdString;
    }

    /**
     * @param string $contentBlockIdString
     * @return ContentBlockId
     */
    public static function fromString($contentBlockIdString)
    {
        if (!is_string($contentBlockIdString)) {
            throw new InvalidContentBlockIdException(
                sprintf('Content block ID can only be created from a string, got %s.', gettype($contentBlockIdString))
            );
        }

        return new self($contentBlockIdString);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->contentBlockIdString;
    }
}
