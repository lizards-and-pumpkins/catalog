<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Command;

class UpdateContentBlockCommand implements Command
{
    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    public function __construct(ContentBlockSource $contentBlockSource)
    {
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
