<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Listing\Exception\DuplicateProductListingAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingCriterionAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingProductListingAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\MissingUrlKeyXmlAttributeException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;
use LizardsAndPumpkins\Import\XPathParser;

class ProductListingBuilder
{
    public function createProductListingFromXml(string $xml, DataVersion $dataVersion) : ProductListing
    {
        $parser = new XPathParser($xml);

        $urlKeyNode = $parser->getXmlNodesArrayByXPath('/listing/@url_key');
        $urlKeyString = $this->getUrlKeyStringFromDomNodeArray($urlKeyNode);
        $urlKey = UrlKey::fromString($urlKeyString);

        $xmlNodeAttributes = $parser->getXmlNodesArrayByXPath('/listing/@*');
        $contextData = $this->getFormattedContextData($xmlNodeAttributes, $dataVersion);

        $criteriaNodes = $parser->getXmlNodesArrayByXPath('/listing/criteria');

        if (count($criteriaNodes) !== 1) {
            throw new InvalidNumberOfCriteriaXmlNodesException(
                'Product listing XML must contain exactly one "criteria" node.'
            );
        }

        $criteria = $this->createSearchCriteria($criteriaNodes[0]);

        $attributesNodes = $parser->getXmlNodesArrayByXPath('/listing/attributes');
        $productListingAttributeList = $this->createProductListingAttributeList($attributesNodes);

        return new ProductListing($urlKey, $contextData, $productListingAttributeList, $criteria);
    }

    /**
     * @param mixed[] $urlKeyAttributeNode
     * @return string
     */
    private function getUrlKeyStringFromDomNodeArray(array $urlKeyAttributeNode) : string
    {
        if (count($urlKeyAttributeNode) === 0) {
            throw new MissingUrlKeyXmlAttributeException('"url_key" attribute is missing in product listing XML.');
        }

        return $urlKeyAttributeNode[0]['value'];
    }

    /**
     * @param array[] $xmlNodeAttributes
     * @param DataVersion $dataVersion
     * @return string[]
     */
    private function getFormattedContextData(array $xmlNodeAttributes, DataVersion $dataVersion) : array
    {
        return array_reduce($xmlNodeAttributes, function (array $carry, array $xmlAttribute) {
            if (Product::URL_KEY !== $xmlAttribute['nodeName']) {
                $carry[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
            }
            return $carry;
        }, [DataVersion::CONTEXT_CODE => (string) $dataVersion]);
    }

    /**
     * @param array[] $criteriaNode
     * @return CompositeSearchCriterion
     */
    private function createSearchCriteria(array $criteriaNode) : CompositeSearchCriterion
    {
        $this->validateCriteriaNode($criteriaNode);

        $criterionArray = array_map(function (array $childNode) {
            if ('criteria' === $childNode['nodeName']) {
                return $this->createSearchCriteria($childNode);
            }

            $this->validateCriterionNode($childNode);
            return $this->createCriterion($childNode);
        }, $criteriaNode['value']);

        return CompositeSearchCriterion::create($criteriaNode['attributes']['type'], ...$criterionArray);
    }

    /**
     * @param array[] $criterionNode
     * @return SearchCriteria
     */
    private function createCriterion(array $criterionNode) : SearchCriteria
    {
        $className = $this->getCriterionClassNameForOperation($criterionNode['attributes']['is']);
        return new $className($criterionNode['attributes']['name'], $criterionNode['value']);
    }

    /**
     * @param array[] $criteriaNode
     */
    private function validateCriteriaNode(array $criteriaNode): void
    {
        if (!isset($criteriaNode['attributes']['type'])) {
            throw new MissingTypeXmlAttributeException('Missing "type" attribute in product listing XML.');
        }
    }

    /**
     * @param array[] $criterionNode
     */
    private function validateCriterionNode(array $criterionNode): void
    {
        if (!isset($criterionNode['attributes']['name'])) {
            throw new MissingCriterionAttributeNameXmlAttributeException(
                'Missing "name" attribute in product listing type XML node.'
            );
        }

        if (!isset($criterionNode['attributes']['is'])) {
            throw new MissingCriterionOperationXmlAttributeException(
                'Missing "is" attribute in product listing type XML node.'
            );
        }

        $operation = $criterionNode['attributes']['is'];

        if (!class_exists($this->getCriterionClassNameForOperation($operation))) {
            throw new InvalidCriterionOperationXmlAttributeException(
                sprintf('Unknown criterion operation "%s"', $operation)
            );
        }
    }

    private function getCriterionClassNameForOperation(string $operationName) : string
    {
        return '\\LizardsAndPumpkins\\DataPool\\SearchEngine\\SearchCriteria\\SearchCriterion' . $operationName;
    }

    /**
     * @param array[] $xmlNodes
     * @return ProductListingAttributeList
     */
    private function createProductListingAttributeList(array $xmlNodes) : ProductListingAttributeList
    {
        $attributesArray = $this->getAttributesFromXmlNodes($xmlNodes);
        return ProductListingAttributeList::fromArray($attributesArray);
    }

    /**
     * @param array[] $xmlNodes
     * @return mixed[]
     */
    private function getAttributesFromXmlNodes(array $xmlNodes) : array
    {
        if (count($xmlNodes) === 0) {
            return [];
        }

        return array_reduce($xmlNodes[0]['value'], function (array $carry, array $attributeXmlNode) {
            if (!isset($attributeXmlNode['attributes']['name'])) {
                throw new MissingProductListingAttributeNameXmlAttributeException(
                    'Missing "name" attribute in product listing "attribute" XML node.'
                );
            }

            $attributeCode = $attributeXmlNode['attributes']['name'];

            if (isset($carry[$attributeCode])) {
                throw new DuplicateProductListingAttributeException(
                    sprintf('Attribute "%s" is encountered more than once in product listing XML.', $attributeCode)
                );
            }

            return array_merge($carry, [$attributeCode => $attributeXmlNode['value']]);
        }, []);
    }
}
