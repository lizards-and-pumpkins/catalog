<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidNumberOfCriteriaXmlNodesException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingProductListingAttributeNameXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingTypeXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;
use LizardsAndPumpkins\UrlKey;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductListingBuilder
{
    /**
     * @param string $xml
     * @param DataVersion $dataVersion
     * @return ProductListing
     */
    public function createProductListingFromXml($xml, DataVersion $dataVersion)
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
    private function getUrlKeyStringFromDomNodeArray(array $urlKeyAttributeNode)
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
    private function getFormattedContextData(array $xmlNodeAttributes, DataVersion $dataVersion)
    {
        return array_reduce($xmlNodeAttributes, function (array $carry, array $xmlAttribute) {
            if (Product::URL_KEY !== $xmlAttribute['nodeName']) {
                $carry[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
            }
            return $carry;
        }, [ContextVersion::CODE => (string) $dataVersion]);
    }

    /**
     * @param array[] $criteriaNode
     * @return CompositeSearchCriterion
     */
    private function createSearchCriteria(array $criteriaNode)
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
     * @return SearchCriterion
     */
    private function createCriterion(array $criterionNode)
    {
        $className = $this->getCriterionClassNameForOperation($criterionNode['attributes']['is']);
        return call_user_func([$className, 'create'], $criterionNode['attributes']['name'], $criterionNode['value']);
    }

    /**
     * @param array[] $criteriaNode
     */
    private function validateCriteriaNode(array $criteriaNode)
    {
        if (!isset($criteriaNode['attributes']['type'])) {
            throw new MissingTypeXmlAttributeException('Missing "type" attribute in product listing XML.');
        }
    }

    /**
     * @param array[] $criterionNode
     */
    private function validateCriterionNode(array $criterionNode)
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

    /**
     * @param string $operationName
     * @return string
     */
    private function getCriterionClassNameForOperation($operationName)
    {
        return SearchCriterion::class . $operationName;
    }

    /**
     * @param array[] $xmlNodes
     * @return ProductListingAttributeList
     */
    private function createProductListingAttributeList(array $xmlNodes)
    {
        $attributesArray = $this->getAttributesFromXmlNodes($xmlNodes);
        return ProductListingAttributeList::fromArray($attributesArray);
    }

    /**
     * @param array[] $xmlNodes
     * @return mixed[]
     */
    private function getAttributesFromXmlNodes(array $xmlNodes)
    {
        if (count($xmlNodes) === 0) {
            return [];
        }

        return @array_reduce($xmlNodes[0]['value'], function (array $carry, array $attributeXmlNode) {
            if (!isset($attributeXmlNode['attributes']['name'])) {
                throw new MissingProductListingAttributeNameXmlAttributeException(
                    'Missing "name" attribute in product listing "attribute" XML node.'
                );
            }
            return array_merge($carry, [$attributeXmlNode['attributes']['name'] => $attributeXmlNode['value']]);
        }, []);
    }
}
