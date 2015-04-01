<?php

namespace Brera\ImageImport;

use Brera\DomainEvent;

class ImportImageDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $xml;

    /**
     * @param $xml
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
