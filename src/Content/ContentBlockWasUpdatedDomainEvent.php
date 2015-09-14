<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\DomainEvent;

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
