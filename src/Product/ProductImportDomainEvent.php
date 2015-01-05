<?php

namespace Brera\Product;

use Brera\DomainEvent;

class ProductImportDomainEvent implements DomainEvent, \Serializable
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

	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->getXml());
	}

	/**
	 * @param string $xml
	 * @return null
	 */
	public function unserialize($xml)
	{
		$this->xml = unserialize($xml);
	}
}
