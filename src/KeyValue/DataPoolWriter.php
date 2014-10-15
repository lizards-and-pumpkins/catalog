<?php


namespace Brera\PoC;


class DataPoolWriter
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param KeyValueStore $keyValueStore
     * @param KeyValueStoreKeyGenerator $keyGenerator
     */
    public function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param ProductId $productId
     * @param string $html
     */
    public function setPoCProductHtml(ProductId $productId, $html)
    {
        $this->keyValueStore->set(
            $this->keyGenerator->createPoCProductHtmlKey($productId),
            $html
        );
    }

    /**
     * @param ProductId $productId
     * @param HttpUrl $seoUrl
     */
    public function setProductIdBySeoUrl(ProductId $productId, HttpUrl $seoUrl)
    {
        $this->keyValueStore->set(
            $this->keyGenerator->createPocProductSeoUrlToIdKey($seoUrl),
            $productId
        );
    }
} 
