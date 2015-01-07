<?php

namespace Brera;

class DomDocumentXPathParser implements XPathParser
{
	/**
	 * @var \DOMDocument
	 */
	private $document;

	/**
	 * @var \DOMXPath
	 */
	private $xPathEngine;

	/**
	 * @var string
	 */
	private $namespacePrefix;

	/**
	 * @var string
	 */
	private $namespacePrefixDefault = 'uniqueDomParserPrefix';

	/**
	 * @param string $xmlString
	 */
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

		$this->xPathEngine = new \DOMXPath($this->document);

		if ($namespaceUri = $this->getNamespaceUri()) {
			$this->xPathEngine->registerNamespace($this->namespacePrefixDefault, $namespaceUri);
			$this->namespacePrefix = $this->namespacePrefixDefault;
		}
	}

	/**
	 * @param string $xPathString
	 * @return array
	 */
	public function getXPathNode($xPathString)
	{
		$nodeArray = [];
		$nodeList = $this->getXPathNodeList($xPathString);

		foreach ($nodeList as $node) {
			$nodeArray[] = [
				'attributes'    => $this->getNodeAttributesAsArray($node),
				'value'         => $node->nodeValue
			];
		}

		return $nodeArray;
	}

	/**
	 * @param string $xPathString
	 * @return array
	 */
	public function getXPathNodeXml($xPathString)
	{
		$nodeXmlArray = [];
		$nodeList = $this->getXPathNodeList($xPathString);

		foreach ($nodeList as $node) {
			$nodeXmlArray[] = $this->document->saveXML($node);
		}

		return $nodeXmlArray;
	}

	/**
	 * @param $xPathString
	 * @return \DOMNodeList
	 */
	private function getXPathNodeList($xPathString)
	{
		$xPathString = $this->addNamespacePrefixesToXPathString($xPathString);
		$nodeList = $this->xPathEngine->query($xPathString);

		return $nodeList;
	}

	/**
	 * @return string
	 */
	private function getNamespaceUri()
	{
		$namespaceUri = $this->document->documentElement->lookupNamespaceUri(null);

		return $namespaceUri;
	}

	/**
	 * @param string $xPathString
	 * @return string
	 */
	private function addNamespacePrefixesToXPathString($xPathString)
	{
		if ($this->namespacePrefix) {
			$xPathString = preg_replace('/(\/|^)([^@])/', '$1' . $this->namespacePrefix . ':$2', $xPathString);
		}

		return $xPathString;
	}

	/**
	 * @param \DOMNode $node
	 * @return array
	 */
	private function getNodeAttributesAsArray(\DOMNode $node)
	{
		if ($node instanceof \DOMAttr) {
			return [$node->name => $node->value];
		}

		$attributeArray = [];

		foreach ($node->attributes as $attributeName => $attributeNode) {
			$attributeArray[$attributeName] = $attributeNode->nodeValue;
		}

		return $attributeArray;
	}
}
