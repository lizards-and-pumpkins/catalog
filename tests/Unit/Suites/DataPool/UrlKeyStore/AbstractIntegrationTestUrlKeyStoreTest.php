<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionIsNotAStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyIsNotAStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyToWriteIsEmptyStringException;
use LizardsAndPumpkins\Utils\Clearable;

abstract class AbstractIntegrationTestUrlKeyStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @return UrlKeyStore
     */
    abstract protected function createUrlKeyStoreInstance();

    protected function setUp()
    {
        $this->urlKeyStore = $this->createUrlKeyStoreInstance();
    }

    public function testItImplementsUrlKeyStore()
    {
        $this->assertInstanceOf(UrlKeyStore::class, $this->urlKeyStore);
    }

    public function testItImplementsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->urlKeyStore);
    }

    public function testItThrowsAnExceptionIfTheUrkKeyToAddIsNotAString()
    {
        $this->setExpectedException(
            UrlKeyIsNotAStringException::class,
            'URL keys have to be strings for storage in the UrlKeyStore, got '
        );
        $this->urlKeyStore->addUrlKeyForVersion(123, '1.0');
    }

    public function testItThrowsAnExceptionIfAVersionToAddIsNotAString()
    {
        $this->setExpectedException(
            DataVersionIsNotAStringException::class,
            'The data version has to be string for use with the UrlKeyStore, got '
        );
        $this->urlKeyStore->addUrlKeyForVersion('test.html', 123);
    }

    public function testItThrowsAnExceptionIfTheUrlKeyIsEmpty()
    {
        $this->setExpectedException(
            UrlKeyToWriteIsEmptyStringException::class,
            'Invalid URL key: url key strings have to be one or more characters long'
        );
        $this->urlKeyStore->addUrlKeyForVersion('', '1.0');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetUrlKeysForIsNotAString()
    {
        $this->setExpectedException(
            DataVersionIsNotAStringException::class,
            'The data version has to be string for use with the UrlKeyStore, got '
        );
        $this->urlKeyStore->getForDataVersion(555);
    }

    public function testItThrowsAnExceptionIfADataVersionToWriteIsAnEmptyString()
    {
        $this->setExpectedException(
            DataVersionToWriteIsEmptyStringException::class,
            'Invalid data version: version strings have to be one or more characters long'
        );
        $this->urlKeyStore->addUrlKeyForVersion('test.html', '');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetIsAnEmptyString()
    {
        $this->setExpectedException(
            DataVersionToWriteIsEmptyStringException::class,
            'Invalid data version: version strings have to be one or more characters long'
        );
        $this->urlKeyStore->getForDataVersion('');
    }

    public function testItReturnsUrlKeysForAGivenVersion()
    {
        $testUrlKey = 'example.html';
        $testVersion = '1.0';
        $this->urlKeyStore->addUrlKeyForVersion($testUrlKey, $testVersion);
        $this->assertSame([$testUrlKey], $this->urlKeyStore->getForDataVersion($testVersion));
    }

    public function testItReturnsAnEmptyArrayForUnknownVersions()
    {
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1.0'));
    }

    public function testItReturnsTheUrlKeysForTheGivenVersion()
    {
        $this->urlKeyStore->addUrlKeyForVersion('aaa.html', '1');
        $this->urlKeyStore->addUrlKeyForVersion('bbb.html', '2');

        $this->assertSame(['aaa.html'], $this->urlKeyStore->getForDataVersion('1'));
        $this->assertSame(['bbb.html'], $this->urlKeyStore->getForDataVersion('2'));
    }

    public function testItClearsTheStorage()
    {
        $this->urlKeyStore->addUrlKeyForVersion('aaa.html', '1');
        $this->urlKeyStore->addUrlKeyForVersion('bbb.html', '1');
        $this->urlKeyStore->clear();
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1'));
    }
}
