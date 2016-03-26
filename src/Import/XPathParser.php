<?php

namespace LizardsAndPumpkins\Import;

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
        $this->validateNoErrors();

        libxml_use_internal_errors($internal);

        $this->xPathEngine = new \DOMXPath($this->document);

        $this->removeCommentNodes();

        if ($namespaceUri = $this->getNamespaceUri()) {
            $this->xPathEngine->registerNamespace($this->namespacePrefixDefault, $namespaceUri);
            $this->namespacePrefix = $this->namespacePrefixDefault;
        }
    }

    /**
     * @param string $xPath
     * @return array[]
     */
    public function getXmlNodesArrayByXPath($xPath)
    {
        $nodeList = $this->getDomNodeListByXPath($xPath);

        return $this->getDomTreeAsArray($nodeList);
    }

    /**
     * @param \DOMNodeList $nodeList
     * @return array[]
     */
    private function getDomTreeAsArray(\DOMNodeList $nodeList)
    {
        $nodeArray = [];

        foreach ($nodeList as $node) {
            $value = $node->nodeValue;

            if (is_a($node->firstChild, '\DOMNode') && XML_ELEMENT_NODE === $node->firstChild->nodeType) {
                $value = $this->getDomTreeAsArray($node->childNodes);
            }

            $nodeArray[] = [
                'nodeName' => $node->nodeName,
                'attributes' => $this->getNodeAttributesAsArray($node),
                'value' => $value
            ];
        }

        return $nodeArray;
    }

    /**
     * @param string $xPath
     * @return string[]
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
     * @param string $xPath
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
            $xPathString = preg_replace('#(/|^)([^@.+/])#', '$1' . $this->namespacePrefix . ':$2', $xPathString);
        }

        return $xPathString;
    }

    /**
     * @param \DOMNode $node
     * @return string[]
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

    private function removeCommentNodes()
    {
        foreach ($this->xPathEngine->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
    }

    private function validateNoErrors()
    {
        $errors = libxml_get_errors();
        if (count($errors) > 0) {
            $message = array_reduce($errors, function ($carry, $error) {
                return $carry . sprintf('XML error on line %d: %s', $error->line, $error->message);
            }, '');
            throw new \OutOfBoundsException($message);
        }
    }
}
