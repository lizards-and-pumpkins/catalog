<?php

namespace Brera\Context;

use Brera\Context\Stubs\StubContextSource;

/**
 * @covers \Brera\Context\ContextSource
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
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
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
     */
    public function testOnlyDesiredPartsArePassedToContextBuilder($partsToExtract, $expectedContextMatrix)
    {
        $this->stubContextBuilder->expects($this->once())
            ->method('createContextsFromDataSets')
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
}
