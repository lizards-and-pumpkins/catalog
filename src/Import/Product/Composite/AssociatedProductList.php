<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Import\Product\ProductDTO;

class AssociatedProductList implements \JsonSerializable, \IteratorAggregate, \Countable
{
    const PHP_CLASSES = 'product_php_classes';
    const PRODUCTS = 'products';

    /**
     * @var ProductDTO[]
     */
    private $products;

    public function __construct(ProductDTO ...$products)
    {
        $this->validateAssociatedProducts(...$products);
        $this->products = $products;
    }

    private function validateAssociatedProducts(ProductDTO ...$products)
    {
        array_reduce($products, function (array $idStrings, ProductDTO $product) {
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
     * @return AssociatedProductList
     */
    public static function fromArray(array $sourceArray)
    {
        $associatedProducts = self::createAssociatedProductsFromArray($sourceArray);
        return new self(...$associatedProducts);
    }

    /**
     * @param array[] $sourceArray
     * @return ProductDTO[]
     */
    private static function createAssociatedProductsFromArray(array $sourceArray)
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
     * @return ProductDTO
     */
    private static function createAssociatedProductFromArray($class, array $productSourceArray)
    {
        return forward_static_call([$class, 'fromArray'], $productSourceArray);
    }

    /**
     * @return ProductDTO[]
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
        return array_map(function (ProductDTO $product) {
            return get_class($product);
        }, $this->products);
    }
    
    /**
     * @return ProductDTO[]
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
        array_reduce($this->products, function ($carry, ProductDTO $product) use ($attributeCodes) {
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
     * @param ProductDTO $product
     * @param string[] $attributeCodes
     * @return array[]
     */
    private function getAttributeValuesForProduct(ProductDTO $product, array $attributeCodes)
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
        every($this->products, function (ProductDTO $product) use ($attributeCodes) {
            $this->validateProductHasAttributes($product, $attributeCodes);
        });
    }

    /**
     * @param ProductDTO $product
     * @param string[] $attributeCodes
     */
    private function validateProductHasAttributes(ProductDTO $product, array $attributeCodes)
    {
        every($attributeCodes, function ($attributeCode) use ($product) {
            $this->validateProductHasAttribute($product, $attributeCode);
        });
    }

    /**
     * @param ProductDTO $product
     * @param string $attributeCode
     */
    private function validateProductHasAttribute(ProductDTO $product, $attributeCode)
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
