<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 */
class ContextVersionTest extends TestCase
{
    private $testCurrentVersion = '1234';

    /**
     * @var ContextVersion
     */
    private $contextVersion;

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubRequestWithRequestedVersion(string $version): HttpRequest
    {
        $stubRequest = $this->createMock(HttpRequest::class);
        $stubRequest->method('hasQueryParameter')->with(ContextVersion::DATA_VERSION_REQUEST_PARAM)->willReturn(true);
        $stubRequest->method('getQueryParameter')->with(ContextVersion::DATA_VERSION_REQUEST_PARAM)->willReturn($version);

        return $stubRequest;
    }

    protected function setUp()
    {
        /** @var DataVersion|\PHPUnit_Framework_MockObject_MockObject $stubCurrentDataVersion */
        $stubCurrentDataVersion = $this->createMock(DataVersion::class);
        $stubCurrentDataVersion->method('__toString')->willReturn($this->testCurrentVersion);
        $this->contextVersion = new ContextVersion($stubCurrentDataVersion);
    }

    public function testIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextVersion);
    }

    public function testReturnsTheCode()
    {
        $this->assertSame(DataVersion::CONTEXT_CODE, $this->contextVersion->getCode());
    }

    public function testReturnsTheVersionFromTheInputArrayIfPresent()
    {
        $inputDataSet = [DataVersion::CONTEXT_CODE => '1.0'];
        $this->assertSame('1.0', $this->contextVersion->getValue($inputDataSet));
    }

    public function testReturnsTheVersionFromTheRequestParametersIfPresent()
    {
        $version = 'foo';
        $inputDataSet = [ContextBuilder::REQUEST => $this->createStubRequestWithRequestedVersion($version)];
        $this->assertSame($version, $this->contextVersion->getValue($inputDataSet));
    }

    public function testReturnsTheInjectedDataVersionValueIfTheInputContainsNoVersion()
    {
        $inputDataSet = [];
        $this->assertSame($this->testCurrentVersion, $this->contextVersion->getValue($inputDataSet));
    }

    public function testTheVersionFromTheInputArrayHasTheHighestPriority()
    {
        $inputDataSet = [
            DataVersion::CONTEXT_CODE => 'foo',
            ContextBuilder::REQUEST   => $this->createStubRequestWithRequestedVersion('bar'),
        ];
        $this->assertSame('foo', $this->contextVersion->getValue($inputDataSet));
    }
}
