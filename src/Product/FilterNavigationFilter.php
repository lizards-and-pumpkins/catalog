<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterCodeException;
use LizardsAndPumpkins\Renderer\Translation\Translator;

class FilterNavigationFilter implements \JsonSerializable
{
    /**
     * @var string
     */
    private $filterCode;

    /**
     * @var FilterNavigationFilterOptionCollection
     */
    private $filterOptionCollection;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param string $filterCode
     * @param FilterNavigationFilterOptionCollection $filterOptionCollection
     * @param Translator $translator
     */
    private function __construct(
        $filterCode,
        FilterNavigationFilterOptionCollection $filterOptionCollection,
        Translator $translator
    ) {
        $this->filterCode = $filterCode;
        $this->filterOptionCollection = $filterOptionCollection;
        $this->translator = $translator;
    }

    /**
     * @param string $filterCode
     * @param FilterNavigationFilterOptionCollection $filterOptionCollection
     * @param Translator $translator
     * @return FilterNavigationFilter
     */
    public static function create(
        $filterCode,
        FilterNavigationFilterOptionCollection $filterOptionCollection,
        Translator $translator
    ) {
        if (!is_string($filterCode)) {
            throw new InvalidFilterNavigationFilterCodeException(
                sprintf('Filter code must be a string, got "%s".', gettype($filterCode))
            );
        }

        return new self($filterCode, $filterOptionCollection, $translator);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->filterCode;
    }

    /**
     * @return FilterNavigationFilterOptionCollection
     */
    public function getOptionCollection()
    {
        return $this->filterOptionCollection;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->filterCode,
            'label' => $this->translator->translate($this->filterCode),
            'options' => $this->filterOptionCollection->jsonSerialize()
        ];
    }
}
