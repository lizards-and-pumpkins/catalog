<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAvailability;

class AssociatedProductList implements \JsonSerializable, \IteratorAggregate, \Countable
{
    const PHP_CLASSES = 'product_php_classes';
    const PRODUCTS = 'products';

    /**
     * @var Product[]
     */
    private $products;

    public function __construct(Product ...$products)
    {
        $this->validateAssociatedProducts(...$products);
        $this->products = $products;
    }

    private function validateAssociatedProducts(Product ...$products)
    {
        array_reduce($products, function (array $idStrings, Product $product) {
            $productIdString = (string) $product->getId();
            if (in_array($productIdString, $idStrings)) {
                throw $this->createDuplicateAssociatedProductException($productIdString);
            }
            return array_merge($idStrings, [$productIdString]);
        }, []);
    }

    /**
     * @param string $productIdString
     * @return DuplicateAssociatedProductException
     */
    private function createDuplicateAssociatedProductException($productIdString)
    {
        $message = sprintf('The product "%s" is associated two times to the same composite product', $productIdString);
        return new DuplicateAssociatedProductException($message);
    }

    /**
     * @param array[] $sourceArray
     * @param ProductAvailability $productAvailability
     * @return AssociatedProductList
     */
    public static function fromArray(array $sourceArray, ProductAvailability $productAvailability)
    {
        $associatedProducts = self::createAssociatedProductsFromArray($sourceArray, $productAvailability);
        return new self(...$associatedProducts);
    }

    /**
     * @param array[] $sourceArray
     * @param ProductAvailability $productAvailability
     * @return Product[]
     */
    private static function createAssociatedProductsFromArray(
        array $sourceArray,
        ProductAvailability $productAvailability
    ) {
        return array_map(function ($idx) use ($sourceArray, $productAvailability) {
            $class = $sourceArray[self::PHP_CLASSES][$idx];
            $productSourceArray = $sourceArray[self::PRODUCTS][$idx];
            return self::createAssociatedProductFromArray($class, $productSourceArray, $productAvailability);
        }, array_keys($sourceArray[self::PRODUCTS]));
    }

    /**
     * @param string $class
     * @param mixed[] $productSourceArray
     * @param ProductAvailability $productAvailability
     * @return Product
     */
    private static function createAssociatedProductFromArray(
        $class,
        array $productSourceArray,
        ProductAvailability $productAvailability
    ) {
        return forward_static_call([$class, 'fromArray'], $productSourceArray, $productAvailability);
    }

    /**
     * @return Product[]
     */
    public function jsonSerialize()
    {
        return [
            self::PHP_CLASSES => $this->getAssociatedProductClassNames(),
            self::PRODUCTS => $this->getProducts()
        ];
    }

    /**
     * @return string[]
     */
    private function getAssociatedProductClassNames()
    {
        return array_map(function (Product $product) {
            return get_class($product);
        }, $this->products);
    }
    
    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->products);
    }

    /**
     * @param string[] $attributeCodes
     */
    public function validateUniqueValueCombinationForEachProductAttribute(...$attributeCodes)
    {
        $this->validateAllProductsHaveTheAttributes(...$attributeCodes);
        array_reduce($this->products, function ($carry, Product $product) use ($attributeCodes) {
            $attributeValuesForProduct = $this->getAttributeValuesForProduct($product, $attributeCodes);
            $otherProductId = array_search($attributeValuesForProduct, $carry);
            if (false !== $otherProductId) {
                throw $this->createProductAttributeValueCombinationNotUniqueException(
                    $otherProductId,
                    (string) $product->getId(),
                    ...$attributeCodes
                );
            }
            return array_merge($carry, [(string) $product->getId() => $attributeValuesForProduct]);
        }, []);
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     * @return array[]
     */
    private function getAttributeValuesForProduct(Product $product, array $attributeCodes)
    {
        return array_reduce($attributeCodes, function ($carry, $attributeCode) use ($product) {
            $allValuesOfAttribute = $product->getAllValuesOfAttribute($attributeCode);
            return array_merge($carry, [(string) $attributeCode => $allValuesOfAttribute]);
        }, []);
    }

    /**
     * @param string $productId1
     * @param string $productId2
     * @param string[] $attrCodes
     * @return ProductAttributeValueCombinationNotUniqueException
     */
    private function createProductAttributeValueCombinationNotUniqueException($productId1, $productId2, ...$attrCodes)
    {
        $message = sprintf(
            'The associated products "%s" and "%s" have the same value combination for the attributes "%s"',
            $productId1,
            $productId2,
            implode('" and "', $attrCodes)
        );
        return new ProductAttributeValueCombinationNotUniqueException($message);
    }

    /**
     * @param string[] $attributeCodes
     */
    private function validateAllProductsHaveTheAttributes(...$attributeCodes)
    {
        every($this->products, function (Product $product) use ($attributeCodes) {
            $this->validateProductHasAttributes($product, $attributeCodes);
        });
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     */
    private function validateProductHasAttributes(Product $product, array $attributeCodes)
    {
        every($attributeCodes, function ($attributeCode) use ($product) {
            $this->validateProductHasAttribute($product, $attributeCode);
        });
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     */
    private function validateProductHasAttribute(Product $product, $attributeCode)
    {
        if (!$product->hasAttribute($attributeCode)) {
            $message = sprintf(
                'The associated product "%s" is missing the required attribute "%s"',
                $product->getId(),
                $attributeCode
            );
            throw new AssociatedProductIsMissingRequiredAttributesException($message);
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->products);
    }
}
