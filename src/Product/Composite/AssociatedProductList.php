<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Product\Composite\Exception\DuplicateAssociatedProductException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductAttributeValueCombinationNotUniqueException;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductIsMissingRequiredAttributesException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductTypeCodeMissingInAssociatedProductListSourceArrayException;
use LizardsAndPumpkins\Product\Composite\Exception\UnknownProductTypeCodeInSourceArrayException;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\SimpleProduct;

class AssociatedProductList implements \JsonSerializable, \IteratorAggregate
{
    /**
     * @var Product[]
     */
    private $products;

    public function __construct(Product ...$products)
    {
        $this->validateAssociatedProductsArray(...$products);
        $this->products = $products;
    }

    private function validateAssociatedProductsArray(Product ...$products)
    {
        array_reduce(
            $products,
            function (array $idStrings, Product $product) {
                $productIdString = (string) $product->getId();
                if (in_array($productIdString, $idStrings)) {
                    throw $this->createDuplicateAssociatedProductException($productIdString);
                }
                return array_merge($idStrings, [$productIdString]);
            },
            []
        );
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
    public static function fromArray($sourceArray)
    {
        $associatedProducts = array_map(
            function (array $productAsArray) {
                self::validateProductTypeCodeIsSet($productAsArray);
                $class = self::locateProductClassByTypeCode($productAsArray[Product::TYPE_KEY]);
                return forward_static_call([$class, 'fromArray'], $productAsArray);
            },
            $sourceArray
        );
        return new self(...$associatedProducts);
    }

    /**
     * @param mixed[] $productAsArray
     */
    private static function validateProductTypeCodeIsSet(array $productAsArray)
    {
        if (!isset($productAsArray[Product::TYPE_KEY])) {
            throw new ProductTypeCodeMissingInAssociatedProductListSourceArrayException(
                'The product type code index is missing from an associated product source array'
            );
        }
    }

    /**
     * @param string $productTypeCode
     */
    private static function locateProductClassByTypeCode($productTypeCode)
    {
        if (SimpleProduct::TYPE_CODE === $productTypeCode) {
            return SimpleProduct::class;
        }
        if (ConfigurableProduct::TYPE_CODE === $productTypeCode) {
            return ConfigurableProduct::class;
        }
        throw new UnknownProductTypeCodeInSourceArrayException(
            sprintf('The product type code "%s" is unknown', $productTypeCode)
        );
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return Product[]
     */
    public function jsonSerialize()
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
     * @param string ...$attributeCodes
     */
    public function validateUniqueValueCombinationForEachProductAttribute(...$attributeCodes)
    {
        $this->validateAllProductsHaveTheAttributes(...$attributeCodes);
        array_reduce(
            $this->products,
            function ($carry, Product $product) use ($attributeCodes) {
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
            },
            []
        );
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     * @return array[]
     */
    private function getAttributeValuesForProduct(Product $product, array $attributeCodes)
    {
        return array_reduce(
            $attributeCodes,
            function ($carry, $attributeCode) use ($product) {
                $allValuesOfAttribute = $product->getAllValuesOfAttribute($attributeCode);
                return array_merge($carry, [(string) $attributeCode => $allValuesOfAttribute]);
            },
            []
        );
    }

    /**
     * @param string $productId1
     * @param string $productId2
     * @param string ...$attrCodes
     * @return ProductAttributeValueCombinationNotUniqueException
     */
    private function createProductAttributeValueCombinationNotUniqueException($productId1, $productId2, ...$attrCodes)
    {
        $message = sprintf(
            'The associated products "%s" and "%s" have the ' .
            'same value combination for the attributes "%s"',
            $productId1,
            $productId2,
            implode('" and "', $attrCodes)
        );
        return new ProductAttributeValueCombinationNotUniqueException($message);
    }

    /**
     * @param string ...$attributeCodes
     */
    private function validateAllProductsHaveTheAttributes(...$attributeCodes)
    {
        array_map(
            function (Product $product) use ($attributeCodes) {
                $this->validateProductHasAttributes($product, $attributeCodes);
            },
            $this->products
        );
    }

    /**
     * @param Product $product
     * @param string[] $attributeCodes
     */
    private function validateProductHasAttributes(Product $product, array $attributeCodes)
    {
        array_map(
            function ($attributeCode) use ($product) {
                $this->validateProductHasAttribute($product, $attributeCode);
            },
            $attributeCodes
        );
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
}
