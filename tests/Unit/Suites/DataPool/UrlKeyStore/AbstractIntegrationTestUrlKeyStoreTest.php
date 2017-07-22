<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;
use LizardsAndPumpkins\Util\Storage\Clearable;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntegrationTestUrlKeyStoreTest extends TestCase
{
    /**
     * @var UrlKeyStore|Clearable
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

    public function testItThrowsAnExceptionIfTheUrlKeyToAddIsNotAString()
    {
        $this->expectException(\TypeError::class);
        $this->urlKeyStore->addUrlKeyForVersion('1.0', 123, 'dummy-context-string', 'type-string');
    }

    public function testItThrowsAnExceptionIfAVersionToAddIsNotAString()
    {
        $this->expectException(\TypeError::class);
        $this->urlKeyStore->addUrlKeyForVersion(123, 'test.html', 'dummy-context-string', 'type-string');
    }

    public function testThatEmptyUrlKeysAreAllowed()
    {
        $testUrlKey = '';
        $testVersion = '1.0';
        $testContext = 'dummy-context-string';
        $testUrlKeyType = 'type-string';
        $this->urlKeyStore->addUrlKeyForVersion($testVersion, $testUrlKey, $testContext, $testUrlKeyType);
        $this->assertSame(
            [[$testUrlKey, $testContext, $testUrlKeyType]],
            $this->urlKeyStore->getForDataVersion($testVersion)
        );
    }

    public function testItThrowsAnExceptionIfADataVersionToGetUrlKeysForIsNotAString()
    {
        $this->expectException(\TypeError::class);
        $this->urlKeyStore->getForDataVersion(555);
    }

    public function testItThrowsAnExceptionIfADataVersionToWriteIsAnEmptyString()
    {
        $this->expectException(DataVersionToWriteIsEmptyStringException::class);
        $this->expectExceptionMessage('Invalid data version: version strings have to be one or more characters long');
        $this->urlKeyStore->addUrlKeyForVersion('', 'test.html', 'dummy-context-string', 'type-string');
    }

    public function testItThrowsAnExceptionIfADataVersionToGetIsAnEmptyString()
    {
        $this->expectException(DataVersionToWriteIsEmptyStringException::class);
        $this->expectExceptionMessage('Invalid data version: version strings have to be one or more characters long');
        $this->urlKeyStore->getForDataVersion('');
    }

    public function testItThrowsAnExceptionIfTheContextIsNotAString()
    {
        $this->expectException(\TypeError::class);
        $this->urlKeyStore->addUrlKeyForVersion('1.0', 'test.html', [], 'type-string');
    }

    public function testItThrowsAnExceptionIfTheUrlKeyTypeIsNotAString()
    {
        $this->expectException(\TypeError::class);
        $this->urlKeyStore->addUrlKeyForVersion('1.0', 'test.html', '', 42);
    }

    public function testItReturnsUrlKeysForAGivenVersion()
    {
        $testUrlKey = 'example.html';
        $testVersion = '1.0';
        $testContext = 'dummy-context-string';
        $testUrlKeyType = 'type-string';
        $this->urlKeyStore->addUrlKeyForVersion($testVersion, $testUrlKey, $testContext, $testUrlKeyType);
        $this->assertSame(
            [[$testUrlKey, $testContext, $testUrlKeyType]],
            $this->urlKeyStore->getForDataVersion($testVersion)
        );
    }

    public function testItReturnsAnEmptyArrayForUnknownVersions()
    {
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1.0'));
    }

    public function testItReturnsTheUrlKeysForTheGivenVersion()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->addUrlKeyForVersion('2', 'bbb.html', 'dummy-context-string', 'type-string');

        $this->assertSame(
            [['aaa.html', 'dummy-context-string', 'type-string']],
            $this->urlKeyStore->getForDataVersion('1')
        );
        $this->assertSame(
            [['bbb.html', 'dummy-context-string', 'type-string']],
            $this->urlKeyStore->getForDataVersion('2')
        );
    }

    public function testItClearsTheStorage()
    {
        $this->urlKeyStore->addUrlKeyForVersion('1', 'aaa.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->addUrlKeyForVersion('1', 'bbb.html', 'dummy-context-string', 'type-string');
        $this->urlKeyStore->clear();
        $this->assertSame([], $this->urlKeyStore->getForDataVersion('1'));
    }
}
