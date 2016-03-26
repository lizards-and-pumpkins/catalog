<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

class ContentBlockSource
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
     * @var mixed[]
     */
    private $keyGeneratorParams;

    /**
     * @param ContentBlockId $contentBlockId
     * @param string $content
     * @param string[] $contextData
     * @param mixed[] $keyGeneratorParams
     */
    public function __construct(ContentBlockId $contentBlockId, $content, array $contextData, array $keyGeneratorParams)
    {
        $this->contentBlockId = $contentBlockId;
        $this->content = $content;
        $this->contextData = $contextData;
        $this->keyGeneratorParams = $keyGeneratorParams;
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

    /**
     * @return mixed[]
     */
    public function getKeyGeneratorParams()
    {
        return $this->keyGeneratorParams;
    }
}
