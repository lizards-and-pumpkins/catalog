<?php

namespace Brera\Content;

use Brera\ProjectionSourceData;

class ContentBlockSource implements ProjectionSourceData
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @param string $identifier
     * @param string $content
     * @param string[] $contextData
     */
    public function __construct($identifier, $content, array $contextData)
    {
        $this->identifier = $identifier;
        $this->content = $content;
        $this->contextData = $contextData;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
