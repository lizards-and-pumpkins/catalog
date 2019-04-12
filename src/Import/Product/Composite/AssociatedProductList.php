<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\Product;

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

    private function createDuplicateAssociatedProductException(
        string $productIdString
    ) : DuplicateAssociatedProductException {
        $message = sprintf('The product "%s" is associated two times to the same composite product', $productIdString);
        return new DuplicateAssociatedProductException($message);
    }

    /**
     * @param array[] $sourceArray
     * @return AssociatedProductList
     */
    public static function fromArray(array $sourceArray) : AssociatedProductList
    {
        $associatedProducts = self::createAssociatedProductsFromArray($sourceArray);
        return new self(...$associatedProducts);
    }

    /**
     * @param array[] $sourceArray
     * @return Product[]
     */
    private static function createAssociatedProductsFromArray(array $sourceArray) : array
    {
        return array_map(function ($idx) use ($sourceArray) {
            $class = $sourceArray[self::PHP_CLASSES][$idx];
            $productSourceArray = $sourceArray[self::PRODUCTS][$idx];
            return self::createAssociatedProductFromArray($class, $productSourceArray);
        }, array_keys($sourceArray[self::PRODUCTS]));
    }

    /**
     * @param string $class
     * @param mixed[] $productSourceArray
     * @return Product
     */
    private static function createAssociatedProductFromArray(string $class, array $productSourceArray) : Product
    {
        return forward_static_call([$class, 'fromArray'], $productSourceArray);
    }

    /**
     * @return Product[]
     */
    public function jsonSerialize() : array
    {
        return [
            self::PHP_CLASSES => $this->getAssociatedProductClassNames(),
            self::PRODUCTS => $this->getProducts()
        ];
    }

    /**
     * @return string[]
     */
    private function getAssociatedProductClassNames() : array
    {
        return array_map(function (Product $product) {
            return get_class($product);
        }, $this->products);
    }
    
    /**
     * @return Product[]
     */
    public function getProducts() : array
    {
        return $this->products;
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->products);
    }

    public function validateUniqueValueCombinationForEachProductAttribute(AttributeCode ...$attributeCodes)
    {
        $this->validateAllProductsHaveTheAttributes(...$attributeCodes);
        array_reduce($this->products, function ($carry, Product $product) use ($attributeCodes) {
            $attributeValuesForProduct = $this->getAttributeValuesForProduct($product, ...$attributeCodes);
            $otherProductId = array_search($attributeValuesForProduct, $carry);
            if (false !== $otherProductId) {
                throw $this->createProductAttributeValueCombinationNotUniqueException(
                    $otherProductId,
                    (string) $product->getId(),
                    ...$attributeCodes
                );
            }
            return ($carry + [(string) $product->getId() => $attributeValuesForProduct]);
        }, []);
    }

    /**
     * @param Product $product
     * @param AttributeCode[] $attributeCodes
     * @return array[]
     */
    private function getAttributeValuesForProduct(Product $product, AttributeCode ...$attributeCodes) : array
    {
        return array_reduce($attributeCodes, function ($carry, AttributeCode $attributeCode) use ($product) {
            $allValuesOfAttribute = $product->getAllValuesOfAttribute((string) $attributeCode);
            return array_merge($carry, [(string) $attributeCode => $allValuesOfAttribute]);
        }, []);
    }

    private function createProductAttributeValueCombinationNotUniqueException(
        string $productId1,
        string $productId2,
        AttributeCode ...$attributeCodes
    ) : ProductAttributeValueCombinationNotUniqueException {
        $message = sprintf(
            'The associated products "%s" and "%s" have the same value combination for the attributes "%s"',
            $productId1,
            $productId2,
            implode('" and "', $attributeCodes)
        );
        return new ProductAttributeValueCombinationNotUniqueException($message);
    }

    private function validateAllProductsHaveTheAttributes(AttributeCode ...$attributeCodes)
    {
        every($this->products, function (Product $product) use ($attributeCodes) {
            $this->validateProductHasAttributes($product, ...$attributeCodes);
        });
    }

    private function validateProductHasAttributes(Product $product, AttributeCode ...$attributeCodes)
    {
        every($attributeCodes, function (AttributeCode $attributeCode) use ($product) {
            $this->validateProductHasAttribute($product, $attributeCode);
        });
    }

    private function validateProductHasAttribute(Product $product, AttributeCode $attributeCode)
    {
        if (! $product->hasAttribute($attributeCode)) {
            $message = sprintf(
                'The associated product "%s" is missing the required attribute "%s"',
                $product->getId(),
                $attributeCode
            );
            throw new AssociatedProductIsMissingRequiredAttributesException($message);
        }
    }

    public function count() : int
    {
        return count($this->products);
    }
}
