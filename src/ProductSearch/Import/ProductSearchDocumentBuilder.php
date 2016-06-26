<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $indexAttributeCodes;

    /**
     * @var AttributeValueCollectorLocator
     */
    private $valueCollectorLocator;

    /**
     * @var TaxableCountries
     */
    private $taxableCountries;

    /**
     * @var TaxServiceLocator
     */
    private $taxServiceLocator;

    /**
     * @param string[] $indexAttributeCodes
     * @param AttributeValueCollectorLocator $valueCollector
     * @param TaxableCountries $taxableCountries
     * @param TaxServiceLocator $taxServiceLocator
     */
    public function __construct(
        array $indexAttributeCodes,
        AttributeValueCollectorLocator $valueCollector,
        TaxableCountries $taxableCountries,
        TaxServiceLocator $taxServiceLocator
    ) {
        $this->indexAttributeCodes = $indexAttributeCodes;
        $this->valueCollectorLocator = $valueCollector;
        $this->taxableCountries = $taxableCountries;
        $this->taxServiceLocator = $taxServiceLocator;
    }

    /**
     * @param ProductDTO $projectionSourceData
     * @return SearchDocument
     */
    public function aggregate($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductDTO)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        return $this->createSearchDocument($projectionSourceData);
    }

    /**
     * @param ProductDTO $product
     * @return SearchDocument
     */
    private function createSearchDocument(ProductDTO $product)
    {
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $product->getContext(), $product->getId());
    }

    /**
     * @param ProductDTO $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(ProductDTO $product)
    {
        $attributesMap = $this->createFieldsForIndexAttributes($product);

        $pricesInclTax = $this->createFieldsForPriceInclTax($product);

        return SearchDocumentFieldCollection::fromArray(
            array_merge($attributesMap, $pricesInclTax, ['product_id' => (string) $product->getId()])
        );
    }

    /**
     * @param ProductDTO $product
     * @return array[]
     */
    private function createFieldsForIndexAttributes(ProductDTO $product)
    {
        return array_reduce($this->indexAttributeCodes, function ($carry, $attributeCode) use ($product) {
            $codeAndValues = [$attributeCode => $this->getAttributeValuesForSearchDocument($product, $attributeCode)];
            return array_merge($carry, $codeAndValues);
        }, []);
    }

    /**
     * @param ProductDTO $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(ProductDTO $product, $attributeCode)
    {
        $collector = $this->valueCollectorLocator->forProduct($product);
        return $collector->getValues($product, AttributeCode::fromString($attributeCode));
    }

    /**
     * @param ProductDTO $product
     * @return array[]
     */
    private function createFieldsForPriceInclTax(ProductDTO $product)
    {
        if (! $product->hasAttribute('price')) {
            return [];
        }

        return array_reduce($this->taxableCountries->getCountries(), function ($carry, $country) use ($product) {
            $priceInclTax = $this->getPriceIncludingTaxForCountry($product, $country);
            $fieldCode = sprintf('price_incl_tax_%s', strtolower($country));
            return array_merge($carry, [$fieldCode => [(string) $priceInclTax]]);
        }, []);
    }

    /**
     * @param ProductDTO $product
     * @param string $countryCode
     * @return Price
     */
    private function getPriceIncludingTaxForCountry(ProductDTO $product, $countryCode)
    {
        $amount = (int) $this->getAttributeValuesForSearchDocument($product, 'price')[0];
        $options = $this->createTaxServiceLocatorOptions($product, $countryCode);
        return $this->taxServiceLocator->get($options)->applyTo(Price::fromFractions($amount));
    }

    /**
     * @param ProductDTO $product
     * @param string $countryCode
     * @return string[]
     */
    private function createTaxServiceLocatorOptions(ProductDTO $product, $countryCode)
    {
        $context = $product->getContext();
        return [
            TaxServiceLocator::OPTION_WEBSITE           => $context->getValue(Website::CONTEXT_CODE),
            TaxServiceLocator::OPTION_PRODUCT_TAX_CLASS => $product->getTaxClass(),
            TaxServiceLocator::OPTION_COUNTRY           => $countryCode,
        ];
    }
}
