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
        ['website' => 'website-one', 'language' => 'english', 'customer_group' => 'general'],
        ['website' => 'website-one', 'language' => 'english', 'customer_group' => 'reseller'],
        ['website' => 'website-one', 'language' => 'german', 'customer_group' => 'general'],
        ['website' => 'website-one', 'language' => 'german', 'customer_group' => 'reseller'],
        ['website' => 'website-two', 'language' => 'english', 'customer_group' => 'general'],
        ['website' => 'website-two', 'language' => 'french', 'customer_group' => 'general'],
        ['website' => 'website-two', 'language' => 'german', 'customer_group' => 'general'],
    ];

    protected function setUp()
    {
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $this->contextSource = new StubContextSource($this->stubContextBuilder, $this->testContextMatrix);
    }

    /**
     * @test
     */
    public function itShouldReturnAnArray()
    {
        $this->stubContextBuilder->expects($this->once())
            ->method('getContexts')
            ->willReturn([]);

        $this->assertInternalType('array', $this->contextSource->getAllAvailableContexts());
    }

    /**
     * @test
     * @dataProvider extractPartsProvider
     */
    public function itShouldReturnOnlyTheDesiredPartsToTheContextBuilder($partsToExtract, $expectedContextMatrix)
    {
        $this->stubContextBuilder->expects($this->once())
            ->method('getContexts')
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
                ['language'],
                [['language' => 'english'], ['language' => 'german'], ['language' => 'french']]
            ],
            [
                ['website', 'customer_group', 'language'],
                $this->testContextMatrix
            ]
        ];
    }
}
