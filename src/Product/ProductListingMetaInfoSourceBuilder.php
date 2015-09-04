<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriterion;
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

        $criteriaConditionNodes = $parser->getXmlNodesArrayByXPath('/listing/@condition');
        $criteria = $this->createSearchCriteria($criteriaConditionNodes);

        $criteriaNodes = $parser->getXmlNodesArrayByXPath('/listing/*');

        foreach ($criteriaNodes as $criterionNode) {
            $criterion = $this->createCriterion($criterionNode);
            $criteria->addCriteria($criterion);
        }

        return new ProductListingMetaInfoSource($urlKey, $contextData, $criteria);
    }

    /**
     * @param mixed[] $urlKeyAttributeNode
     * @return string
     */
    private function getUrlKeyStringFromDomNodeArray(array $urlKeyAttributeNode)
    {
        if (empty($urlKeyAttributeNode)) {
            throw new MissingUrlKeyXmlAttributeException();
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
     * @return CompositeSearchCriterion
     */
    private function createSearchCriteria(array $criteriaCondition)
    {
        if (empty($criteriaCondition)) {
            throw new MissingConditionXmlAttributeException;
        }

        if (CompositeSearchCriterion::AND_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createAnd();
        }

        if (CompositeSearchCriterion::OR_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createOr();
        }

        throw new InvalidConditionXmlAttributeException;
    }

    /**
     * @param array[] $criterionNode
     * @return SearchCriterion
     */
    private function createCriterion(array $criterionNode)
    {
        if (!isset($criterionNode['attributes']['operation'])) {
            throw new MissingCriterionOperationXmlAttributeException();
        }

        return SearchCriterion::create(
            $criterionNode['nodeName'],
            $criterionNode['value'],
            $criterionNode['attributes']['operation']
        );
    }
}
