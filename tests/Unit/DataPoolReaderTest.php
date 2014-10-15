<?php

namespace Brera\PoC;

/**
 * @covers \Brera\PoC\DataPoolReader
 */
class DataPoolReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValueStorage;
    /**
     * @var KeyValueStoreKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValueStoreKeyGenerator;
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    protected function setUp()
    {
        $this->keyValueStorage = $this->getMock(KeyValueStore::class);
        $this->keyValueStoreKeyGenerator = $this->getMock(
            KeyValueStoreKeyGenerator::class
        );

        $this->dataPoolReader = new DataPoolReader(
            $this->keyValueStorage,
            $this->keyValueStoreKeyGenerator
        );
    }

    /**
     * @todo
     */
    public function shouldReturnPoCProductHtmlBasedOnKeyFromKeyValueStorage()
    {

    }
}
