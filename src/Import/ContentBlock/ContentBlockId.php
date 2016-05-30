<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

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
        // todo: guard against empty content block id's
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
