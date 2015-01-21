<?php

namespace Brera;

class XPathParser
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
	 * @param string $xPath
	 * @return array
	 */
	public function getXmlNodesArrayByXPath($xPath)
	{
		$nodeArray = [];
		$nodeList = $this->getDomNodeListByXPath($xPath);

		foreach ($nodeList as $node) {
			$nodeArray[] = [
				'name'          => $node->nodeName,
				'attributes'    => $this->getNodeAttributesAsArray($node),
				'value'         => $this->getXmlNodeValue($node)
			];
		}

		return $nodeArray;
	}

	/**
	 * @param \DOMNode $parent
	 * @return string|array
	 */
	private function getXmlNodeValue(\DOMNode $parent)
	{
		if (!is_null($parent->firstChild) && XML_ELEMENT_NODE !== $parent->firstChild->nodeType) {
			return $parent->nodeValue;
		}

		$value = [];

		foreach ($parent->childNodes as $node) {
			$value[] = [
				'name'          => $node->nodeName,
				'attributes'    => $this->getNodeAttributesAsArray($node),
				'value'         => $this->getXmlNodeValue($node)
			];
		}

		return $value;
	}

	/**
	 * @param string $xPath
	 * @return array
	 */
	public function getXmlNodesRawXmlArrayByXPath($xPath)
	{
		$nodeXmlArray = [];
		$nodeList = $this->getDomNodeListByXPath($xPath);

		foreach ($nodeList as $node) {
			$nodeXmlArray[] = $this->document->saveXML($node);
		}

		return $nodeXmlArray;
	}

	/**
	 * @param $xPath
	 * @return \DOMNodeList
	 */
	private function getDomNodeListByXPath($xPath)
	{
		$xPath = $this->addNamespacePrefixesToXPathString($xPath);
		$nodeList = $this->xPathEngine->query($xPath);

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
