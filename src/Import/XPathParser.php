<?php

declare(strict_types=1);

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

    public function __construct(string $xmlString)
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
    public function getXmlNodesArrayByXPath(string $xPath) : array
    {
        $nodeList = $this->getDomNodeListByXPath($xPath);

        return $this->getDomTreeAsArray($nodeList);
    }

    /**
     * @param \DOMNodeList $nodeList
     * @return array[]
     */
    private function getDomTreeAsArray(\DOMNodeList $nodeList) : array
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
    public function getXmlNodesRawXmlArrayByXPath(string $xPath) : array
    {
        $nodeXmlArray = [];
        $nodeList = $this->getDomNodeListByXPath($xPath);

        foreach ($nodeList as $node) {
            $nodeXmlArray[] = $this->document->saveXML($node);
        }

        return $nodeXmlArray;
    }

    private function getDomNodeListByXPath(string $xPath) : \DOMNodeList
    {
        $xPath = $this->addNamespacePrefixesToXPathString($xPath);
        $nodeList = $this->xPathEngine->query($xPath);

        return $nodeList;
    }

    private function getNamespaceUri(): ?string
    {
        return $this->document->documentElement->lookupNamespaceUri(null);
    }

    private function addNamespacePrefixesToXPathString(string $xPathString) : string
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
    private function getNodeAttributesAsArray(\DOMNode $node) : array
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

    private function removeCommentNodes(): void
    {
        foreach ($this->xPathEngine->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
    }

    private function validateNoErrors(): void
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
