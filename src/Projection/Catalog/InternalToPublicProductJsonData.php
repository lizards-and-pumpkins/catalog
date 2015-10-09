<?php


namespace LizardsAndPumpkins\Projection\Catalog;

class InternalToPublicProductJsonData
{
    /**
     * @param mixed[] $internalProductJsonData
     * @return mixed[]
     */
    public function transformProduct(array $internalProductJsonData)
    {
        $publicProduct = $this->getPublicProductJsonData($internalProductJsonData);

        return isset($internalProductJsonData['simple_product']) ?
            array_merge($this->transformProduct($internalProductJsonData['simple_product']), $publicProduct) :
            $publicProduct;
    }

    /**
     * @param mixed[] $product
     * @return mixed[]
     */
    private function getPublicProductJsonData(array $product)
    {
        return array_reduce(array_keys($product), function (array $carry, $key) use ($product) {
            if ('context' === $key || 'simple_product' === $key) {
                return $carry;
            }
            $transformation = $this->getTransformation($key);
            return array_merge($carry, [$key => $transformation($product[$key])]);
        }, []);
    }

    /**
     * @param string $key
     * @return \Closure
     */
    private function getTransformation($key)
    {
        $method = 'transform' . $this->keyToMethod($key);

        return function ($inputData) use ($method) {
            return method_exists($this, $method) ?
                call_user_func([$this, $method], $inputData) :
                $inputData;
        };
    }

    /**
     * @param string $key
     * @return string
     */
    private function keyToMethod($key)
    {
        return str_replace('_', '', preg_replace_callback('/_([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, $key));
    }

    /**
     * @param array[] $attributes
     * @return array[]
     */
    private function transformAttributes(array $attributes)
    {
        return array_reduce($attributes, function (array $carry, array $attribute) {
            return array_merge($carry, [$attribute['code'] => $this->getAttributeValue($attribute, $carry)]);
        }, []);
    }

    /**
     * @param mixed[] $attribute
     * @param string[] $carry
     * @return string|string[]
     */
    private function getAttributeValue(array $attribute, array $carry)
    {
        $code = $attribute['code'];
        return array_key_exists($code, $carry) ?
            $this->getAttributeValuesAsArray($attribute, $carry[$code]) :
            $attribute['value'];
    }

    /**
     * @param mixed[] $attribute
     * @param string|string[] $existing
     * @return string[]
     */
    private function getAttributeValuesAsArray(array $attribute, $existing)
    {
        $existingValues = is_array($existing) ?
            $existing :
            [$existing];
        return array_merge($existingValues, [$attribute['value']]);
    }

    /**
     * @param array[] $images
     * @return array[]
     */
    private function transformImages(array $images)
    {
        return array_map(function (array $imageAttributeList) {
            return $this->transformAttributes($imageAttributeList);
        }, $images);
    }

    /**
     * @param array[] $internalAssociatedProductsJsonData
     * @return array[]
     */
    public function transformAssociatedProducts(array $internalAssociatedProductsJsonData)
    {
        return array_map(function (array $associatedProduct) {
            return $this->transformProduct($associatedProduct);
        }, $internalAssociatedProductsJsonData['products']);
    }

    /**
     * @param string[] $internalVariationAttributesJsonData
     * @return string[]
     */
    public function transformVariationAttributes(array $internalVariationAttributesJsonData)
    {
        return $internalVariationAttributesJsonData;
    }
}