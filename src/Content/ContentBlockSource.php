<?php

namespace Brera\Content;

use Brera\ProjectionSourceData;

class ContentBlockSource implements ProjectionSourceData
{
    /**
     * @var ContentBlockId
     */
    private $contentBlockId;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @param ContentBlockId $contentBlockId
     * @param string $content
     * @param string[] $contextData
     */
    public function __construct(ContentBlockId $contentBlockId, $content, array $contextData)
    {
        $this->contentBlockId = $contentBlockId;
        $this->content = $content;
        $this->contextData = $contextData;
    }

    /**
     * @return ContentBlockId
     */
    public function getContentBlockId()
    {
        return $this->contentBlockId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function getContextData()
    {
        return $this->contextData;
    }
}
