<?php

namespace Brera\Content;

use Brera\DomainEvent;

class ContentBlockWasUpdatedDomainEvent implements DomainEvent
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
     * @return ContentBlockSource
     */
    public function getContentBlockSource()
    {
        return $this->contentBlockSource;
    }
}
