<?php

namespace Brera\Product;

use Brera\DomainCommand;

class ProjectProductStockQuantitySnippetDomainCommand implements DomainCommand
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
