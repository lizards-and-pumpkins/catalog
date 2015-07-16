<?php

namespace Brera\Content;

use Brera\Command;

class UpdateContentBlockCommand implements Command
{
    /**
     * @var ContentBlockId
     */
    private $contentBlockId;

    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    public function __construct(ContentBlockId $contentBlockId, ContentBlockSource $contentBlockSource)
    {
        $this->contentBlockId = $contentBlockId;
        $this->contentBlockSource = $contentBlockSource;
    }

    /**
     * @return ContentBlockId
     */
    public function getContentBlockId()
    {
        return $this->contentBlockId;
    }

    /**
     * @return ContentBlockSource
     */
    public function getContentBlockSource()
    {
        return $this->contentBlockSource;
    }
}
