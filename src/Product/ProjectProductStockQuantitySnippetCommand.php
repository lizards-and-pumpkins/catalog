<?php

namespace Brera\Product;

use Brera\Command;

class ProjectProductStockQuantitySnippetCommand implements Command
{
    /**
     * @var string
     */
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
