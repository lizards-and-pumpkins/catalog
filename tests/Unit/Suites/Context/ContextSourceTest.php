<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Context\Stub\StubContextSource;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;

/**
 * @covers \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class ContextSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    private $testContextMatrix = [
        ['website' => 'website-one', 'locale' => 'en_US', 'customer_group' => 'general'],
        ['website' => 'website-one', 'locale' => 'en_US', 'customer_group' => 'reseller'],
        ['website' => 'website-one', 'locale' => 'de_DE', 'customer_group' => 'general'],
        ['website' => 'website-one', 'locale' => 'de_DE', 'customer_group' => 'reseller'],
        ['website' => 'website-two', 'locale' => 'en_US', 'customer_group' => 'general'],
        ['website' => 'website-two', 'locale' => 'fr_FR', 'customer_group' => 'general'],
        ['website' => 'website-two', 'locale' => 'de_DE', 'customer_group' => 'general'],
    ];

    protected function setUp()
    {
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class);
        $this->contextSource = new StubContextSource($this->stubContextBuilder, $this->testContextMatrix);
    }

    public function testArrayIsReturned()
    {
        $this->stubContextBuilder->method('createContextsFromDataSets')->willReturn([]);

        $this->assertInternalType('array', $this->contextSource->getAllAvailableContexts());
    }

    public function testAllAvailableContextsAreLazyLoaded()
    {
        $this->stubContextBuilder->expects($this->once())->method('createContextsFromDataSets')->willReturn([]);

        $this->contextSource->getAllAvailableContexts();
        $this->contextSource->getAllAvailableContexts();
    }

    /**
     * @dataProvider extractPartsProvider
     * @param string[] $partsToExtract
     * @param array[] $expectedContextMatrix
     */
    public function testOnlyDesiredPartsArePassedToContextBuilder(array $partsToExtract, array $expectedContextMatrix)
    {
        $this->stubContextBuilder->expects($this->once())->method('createContextsFromDataSets')
            ->with($expectedContextMatrix);

        $this->contextSource->getContextsForParts($partsToExtract);
    }

    /**
     * @return mixed[]
     */
    public function extractPartsProvider()
    {
        return [
            [
                ['website', 'customer_group'],
                [
                    ['website' => 'website-one', 'customer_group' => 'general'],
                    ['website' => 'website-one', 'customer_group' => 'reseller'],
                    ['website' => 'website-two', 'customer_group' => 'general'],
                ]
            ],
            [
                ['locale'],
                [['locale' => 'en_US'], ['locale' => 'de_DE'], ['locale' => 'fr_FR']]
            ],
            [
                ['website', 'customer_group', 'locale'],
                $this->testContextMatrix
            ]
        ];
    }

    public function testItReturnsAllContextsWithTheSpecifiedVersion()
    {
        $testVersion = DataVersion::fromVersionString('abc123');
        $this->stubContextBuilder->expects($this->once())->method('createContextsFromDataSets')
            ->willReturnCallback(function (array $dataSets) use ($testVersion) {
                array_map(function ($dataSet) use ($testVersion) {
                    $this->assertArrayHasKey(ContextVersion::CODE, $dataSet);
                    $this->assertSame($dataSet[ContextVersion::CODE], (string)$testVersion);
                }, $dataSets);
            });
        $this->contextSource->getAllAvailableContextsWithVersion($testVersion);
    }
}
