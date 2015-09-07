<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use Brera\UrlKey;
use Brera\Utils\XPathParser;

class ProductListingMetaInfoSourceBuilder
{
    /**
     * @param string $xml
     * @return ProductListingMetaInfoSource
     */
    public function createProductListingMetaInfoSourceFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $urlKeyNode = $parser->getXmlNodesArrayByXPath('/listing/@url_key');
        $urlKeyString = $this->getUrlKeyStringFromDomNodeArray($urlKeyNode);
        $urlKey = UrlKey::fromString($urlKeyString);

        $xmlNodeAttributes = $parser->getXmlNodesArrayByXPath('/listing/@*');
        $contextData = $this->getFormattedContextData($xmlNodeAttributes);

        $criteriaNodes = $parser->getXmlNodesArrayByXPath('/listing/*');
        $criteriaConditionNodes = $parser->getXmlNodesArrayByXPath('/listing/@condition');

        $criterionArray = array_map([$this, 'createCriterion'], $criteriaNodes);
        $criteria = $this->createSearchCriteria($criteriaConditionNodes, $criterionArray);

        return new ProductListingMetaInfoSource($urlKey, $contextData, $criteria);
    }

    /**
     * @param mixed[] $urlKeyAttributeNode
     * @return string
     */
    private function getUrlKeyStringFromDomNodeArray(array $urlKeyAttributeNode)
    {
        if (empty($urlKeyAttributeNode)) {
            throw new MissingUrlKeyXmlAttributeException('"url_key" attribute is missing in product listing XML.');
        }

        return $urlKeyAttributeNode[0]['value'];
    }

    /**
     * @param array[] $xmlNodeAttributes
     * @return string[]
     */
    private function getFormattedContextData(array $xmlNodeAttributes)
    {
        $contextData = [];

        foreach ($xmlNodeAttributes as $xmlAttribute) {
            if ('url_key' !== $xmlAttribute['nodeName'] && 'condition' !== $xmlAttribute['nodeName']) {
                $contextData[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
            }
        }

        return $contextData;
    }

    /**
     * @param array[] $criteriaCondition
     * @param SearchCriterion[] $criterionArray
     * @return CompositeSearchCriterion
     */
    private function createSearchCriteria(array $criteriaCondition, array $criterionArray)
    {
        if (empty($criteriaCondition)) {
            throw new MissingConditionXmlAttributeException('Missing "condition" attribute in product listing XML.');
        }

        if (CompositeSearchCriterion::AND_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createAnd($criterionArray);
        }

        if (CompositeSearchCriterion::OR_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createOr($criterionArray);
        }

        throw new InvalidConditionXmlAttributeException('Invalid "condition" attribute value in product listing XML.');
    }

    /**
     * @param array[] $criterionNode
     * @return SearchCriterion
     */
    private function createCriterion(array $criterionNode)
    {
        if (!isset($criterionNode['attributes']['operation'])) {
            throw new MissingCriterionOperationXmlAttributeException(
                'Missing "operation" attribute in product listing condition XML node.'
            );
        }

        return SearchCriterion::create(
            $criterionNode['nodeName'],
            $criterionNode['value'],
            $criterionNode['attributes']['operation']
        );
    }
}
