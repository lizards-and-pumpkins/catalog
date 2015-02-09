<?php

namespace Brera\Product;

use Brera\Attribute;
use Brera\Environment\Environment;

class ProductAttribute implements Attribute
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $environment;

    /**
     * @var string|ProductAttributeList
     */
    private $value;

    /**
     * @param string $code
     * @param string|ProductAttributeList $value
     * @param array $environmentData
     */
    private function __construct($code, $value, array $environmentData = [])
    {
        $this->code = $code;
        $this->environment = $environmentData;
        $this->value = $value;
    }

    /**
     * @param array $node
     * @return ProductAttribute
     */
    public static function fromArray(array $node)
    {
        return new self($node['nodeName'], self::getValueRecursive($node['value']), $node['attributes']);
    }

    /**
     * @param array|string $nodeValue
     * @return string|ProductAttributeList
     */
    private static function getValueRecursive($nodeValue)
    {
        if (!is_array($nodeValue)) {
            return $nodeValue;
        }

        $list = new ProductAttributeList();

        foreach ($nodeValue as $node) {
            $list->add(new self($node['nodeName'], self::getValueRecursive($node['value']), $node['attributes']));
        }

        return $list;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $codeExpectation
     * @return bool
     */
    public function isCodeEqualsTo($codeExpectation)
    {
        return $codeExpectation == $this->code;
    }

    /**
     * @return string|ProductAttributeList
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Environment $environment
     * @return int
     */
    public function getMatchScoreForEnvironment(Environment $environment)
    {
        return array_reduce(
            $environment->getSupportedCodes(),
            function ($score, $environmentCode) use ($environment) {
                return $score + $this->getScoreIfEnvironmentIsSetAndMatches(
                    $environmentCode,
                    $environment
                );
            },
            0
        );
    }

    /**
     * @param string $environmentCode
     * @param Environment $environment
     * @return int
     */
    private function getScoreIfEnvironmentIsSetAndMatches($environmentCode, Environment $environment)
    {
        return array_key_exists($environmentCode, $this->environment) ?
            $this->getScoreIfEnvironmentMatches($environmentCode, $environment) :
            0;
    }

    /**
     * @param Environment $environment
     * @return int
     */
    private function getScoreIfEnvironmentMatches($environmentCode, Environment $environment)
    {
        return $environment->getValue($environmentCode) === $this->environment[$environmentCode] ?
            1 :
            0;
    }
}
