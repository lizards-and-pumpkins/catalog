<?php


namespace Brera\PoC;


class KeyValueStoreKeyGenerator
{
    /**
     * Micro-Optimization:
     * Key size matters once there are millions of keys. 
     * Could be a longer string for debugging or a shorter one for production
     * 
     * @param ProductId $productId
     * @return string
     */
    public function createPoCProductHtmlKey(ProductId $productId)
    {
        return 'html_product_poc_' . $productId;
    }
}