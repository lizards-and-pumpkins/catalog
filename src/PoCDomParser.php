<?php

namespace Brera\PoC;

class PoCDomParser implements DomParser
{
	/**
	 * @var \DOMDocument
	 */
	private $document;

	/**
	 * @var \DOMXPath
	 */
	private $xpath;

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var string
	 */
	private $defaultPrefix = 'uniqueDomParserPrefix';

	public function __construct($xmlString)
	{
		libxml_clear_errors();
		$internal = libxml_use_internal_errors(true);

		$this->document = new \DOMDocument;
		$this->document->preserveWhiteSpace = false;
		$this->document->loadXML($xmlString);

		if (!empty(libxml_get_errors())) {
			throw new \OutOfBoundsException();
		}

		libxml_use_internal_errors($internal);
	}

	/**
	 * @param string $xPathString
	 * @param null $contextNode
	 * @param bool $getFirstNode
	 * @return \DOMElement|\DOMNodeList|null
	 */
	public function getXPathNode($xPathString, $contextNode = null, $getFirstNode = false)
	{
		$this->initialiseXPath();
		$xPathString = $this->prepareXPathString($xPathString);

		$nodeList = $this->xpath->query($xPathString, $contextNode);

		if (1 == $nodeList->length || $getFirstNode) {
			return $nodeList->item(0);
		}

		return $nodeList;
	}

	private function initialiseXPath()
	{
		$this->xpath = new \DOMXPath($this->document);

		if ($namespaceUri = $this->document->documentElement->lookupNamespaceUri(null)) {
			$this->xpath->registerNamespace($this->defaultPrefix, $namespaceUri);
			$this->prefix = $this->defaultPrefix;
		}
	}

	/**
	 * @param $xPathString
	 * @return string
	 */
	private function prepareXPathString($xPathString)
	{
		if ($this->prefix) {
			$xPathString = $this->prefix . ':' . str_replace('/', '/' . $this->prefix . ':', $xPathString);
		}

		return $xPathString;
	}
}
