<?php

namespace Brera\Product;

use Brera\DomainEvent;

class CatalogImportDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $xml;

    /**
     * @param string $xml
     */
    public function __construct($xml)
    {
        $this->xml = $xml;
    }

    /**
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }
}
