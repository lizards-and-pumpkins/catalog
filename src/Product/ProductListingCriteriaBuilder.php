<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Exception\DataNotStringException;
use LizardsAndPumpkins\Product\Exception\InvalidConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\InvalidCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingConditionXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingCriterionOperationXmlAttributeException;
use LizardsAndPumpkins\Product\Exception\MissingUrlKeyXmlAttributeException;
use LizardsAndPumpkins\UrlKey;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductListingCriteriaBuilder
{
    /**
     * @param string $xml
     * @param DataVersion $dataVersion
     * @return ProductListingCriteria
     */
    public function createProductListingCriteriaFromXml($xml, DataVersion $dataVersion)
    {
        $parser = new XPathParser($xml);

        $urlKeyNode = $parser->getXmlNodesArrayByXPath('/listing/@url_key');
        $urlKeyString = $this->getUrlKeyStringFromDomNodeArray($urlKeyNode);
        $urlKey = UrlKey::fromString($urlKeyString);

        $xmlNodeAttributes = $parser->getXmlNodesArrayByXPath('/listing/@*');
        $contextData = $this->getFormattedContextData($xmlNodeAttributes, $dataVersion);

        $criteriaNodes = $parser->getXmlNodesArrayByXPath('/listing/*');
        $criteriaConditionNodes = $parser->getXmlNodesArrayByXPath('/listing/@condition');

        $criterionArray = array_map([$this, 'createCriterion'], $criteriaNodes);
        $criteria = $this->createSearchCriteria($criteriaConditionNodes, ...$criterionArray);

        return $this->createProductListingCriteria($urlKey, $contextData, $criteria);
    }

    /**
     * @param UrlKey $urlKey
     * @param string[] $contextData
     * @param SearchCriteria $criteria
     * @return ProductListingCriteria
     */
    public function createProductListingCriteria(UrlKey $urlKey, array $contextData, SearchCriteria $criteria)
    {
        $thingsToCheck = [['values', $contextData], ['keys', array_keys($contextData)]];
        array_map(function (array $thingToCheck) {
            $message = sprintf('The context array has to contain only string %s, found "%%s"', $thingToCheck[0]);
            array_map($this->getStringValidatorWithMessage($message), $thingToCheck[1]);
        }, $thingsToCheck);

        return new ProductListingCriteria($urlKey, $contextData, $criteria);
    }

    /**
     * @param string $message
     * @return callable
     */
    private function getStringValidatorWithMessage($message)
    {
        return function ($data) use ($message) {
            if (!is_string($data)) {
                throw new DataNotStringException(sprintf($message, $this->getTypeOfData($data)));
            }
        };
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
     * @param DataVersion $dataVersion
     * @return \string[]
     */
    private function getFormattedContextData(array $xmlNodeAttributes, DataVersion $dataVersion)
    {
        $contextData = [VersionedContext::CODE => (string) $dataVersion];

        foreach ($xmlNodeAttributes as $xmlAttribute) {
            if (SimpleProduct::URL_KEY !== $xmlAttribute['nodeName'] && 'condition' !== $xmlAttribute['nodeName']) {
                $contextData[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
            }
        }

        return $contextData;
    }

    /**
     * @param array[] $criteriaCondition
     * @param SearchCriterion|SearchCriterion ...$criterionArray
     * @return CompositeSearchCriterion
     */
    private function createSearchCriteria(array $criteriaCondition, SearchCriterion ...$criterionArray)
    {
        if (empty($criteriaCondition)) {
            throw new MissingConditionXmlAttributeException('Missing "condition" attribute in product listing XML.');
        }

        if (CompositeSearchCriterion::AND_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createAnd(...$criterionArray);
        }

        if (CompositeSearchCriterion::OR_CONDITION === $criteriaCondition[0]['value']) {
            return CompositeSearchCriterion::createOr(...$criterionArray);
        }

        throw new InvalidConditionXmlAttributeException(sprintf(
            '"condition" attribute value "%s" in product listing XML is invalid.',
            $criteriaCondition[0]['value']
        ));
    }

    /**
     * @param array[] $criterionNode
     * @return SearchCriterion
     */
    private function createCriterion(array $criterionNode)
    {
        $this->validateSearchCriterionMetaInfo($criterionNode);

        $className = $this->getCriterionClassNameForOperation($criterionNode['attributes']['operation']);

        return call_user_func([$className, 'create'], $criterionNode['nodeName'], $criterionNode['value']);
    }

    /**
     * @param array[] $criterionNode
     */
    private function validateSearchCriterionMetaInfo(array $criterionNode)
    {
        if (!isset($criterionNode['attributes']['operation'])) {
            throw new MissingCriterionOperationXmlAttributeException(
                'Missing "operation" attribute in product listing condition XML node.'
            );
        }
        $this->validateSearchCriteriaOperationString($criterionNode['attributes']['operation']);
    }

    /**
     * @param string $operation
     */
    private function validateSearchCriteriaOperationString($operation)
    {
        if (!preg_match('/^[a-z]+$/i', $operation)) {
            throw new InvalidCriterionOperationXmlAttributeException(sprintf(
                'Invalid operation in product listing XML "%s", only the letters a-z are allowed.',
                $operation
            ));
        }

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
     * @param mixed $data
     * @return string
     */
    private function getTypeOfData($data)
    {
        return is_object($data) ?
            get_class($data) :
            gettype($data);
    }
}
