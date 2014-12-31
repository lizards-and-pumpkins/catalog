<?php

namespace Brera\PoC\Product;

use Brera\PoC\DomainEvent;

class CatalogImportDomainEvent implements DomainEvent, \Serializable
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

	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->getXml());
	}

	/**
	 * @param string $data
	 */
	public function unserialize($data)
	{
		$this->xml = unserialize($data);
	}
} 
