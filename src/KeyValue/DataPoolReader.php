<?php


namespace Brera\PoC;


class DataPoolReader
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyValueStoreKeyGenerator;

    function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyValueStoreKeyGenerator = $keyValueStoreKeyGenerator;
    }

    public function getPoCProductHtml(ProductId $productId)
    {
        return $this->keyValueStore->get(
            $this->keyValueStoreKeyGenerator->createPoCProductHtmlKey($productId)
        );
    }

} 