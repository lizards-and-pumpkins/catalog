<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\Clearable;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore
 */
class InMemoryUrlKeyStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryUrlKeyStore
     */
    private $urlKeyStore;

    protected function setUp()
    {
        $this->urlKeyStore = new InMemoryUrlKeyStore();
    }

    public function testItImplementsUrlKeyStore()
    {
        $this->assertInstanceOf(UrlKeyStore::class, $this->urlKeyStore);
    }

    public function testItImplementsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->urlKeyStore);
    }

    public function testItReturnsUrlKeysForAGivenVersion()
    {
        $testUrlKey = 'example.html';
        $testVersion = '1.0';
        $this->urlKeyStore->addUrlKeyForVersion($testUrlKey, $testVersion);
        $this->assertSame([$testUrlKey], $this->urlKeyStore->getForVersion($testVersion));
    }

    public function testItReturnsAnEmptyArrayForUnknownVersions()
    {
        $this->assertSame([], $this->urlKeyStore->getForVersion('999'));
    }

    public function testItReturnsTheUrlKeysForTheGivenVersion()
    {
        $this->urlKeyStore->addUrlKeyForVersion('aaa', '1');
        $this->urlKeyStore->addUrlKeyForVersion('bbb', '2');
        
        $this->assertSame(['aaa'], $this->urlKeyStore->getForVersion('1'));
        $this->assertSame(['bbb'], $this->urlKeyStore->getForVersion('2'));
    }

    public function testItClearsTheStorage()
    {
        $this->urlKeyStore->addUrlKeyForVersion('aaa', '1');
        $this->urlKeyStore->addUrlKeyForVersion('bbb', '1');
        $this->urlKeyStore->clear();
        $this->assertSame([], $this->urlKeyStore->getForVersion('1'));
    }
}
