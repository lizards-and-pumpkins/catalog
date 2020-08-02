<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Website\Website
 */
class WebsiteTest extends TestCase
{
    public function testItThrowsAnExceptionIfTheInputIsNotAString(): void
    {
        $this->expectException(InvalidWebsiteCodeException::class);
        $this->expectExceptionMessage('The website code must be a string, got "integer"');
        Website::fromString(123);
    }

    /**
     * @dataProvider emptyWebsiteCodeDataProvider
     */
    public function testItThrowsAnExceptionIfTheWebsiteCodeIsEmpty(string $emptyWebsiteCode): void
    {
        $this->expectException(InvalidWebsiteCodeException::class);
        $this->expectExceptionMessage('The website code may not be empty');
        Website::fromString($emptyWebsiteCode);
    }

    /**
     * @return array[]
     */
    public function emptyWebsiteCodeDataProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testItReturnsAWebsite(): void
    {
        $this->assertInstanceOf(Website::class, Website::fromString('test'));
    }

    /**
     * @dataProvider websiteCodeDataProvider
     */
    public function testItReturnsTheWebsiteCodeAsAString(string $websiteCode): void
    {
        $this->assertSame($websiteCode, (string) Website::fromString($websiteCode));
    }

    /**
     * @return array[]
     */
    public function websiteCodeDataProvider() : array
    {
        return [
            ['abc'],
            ['test'],
        ];
    }

    public function testItReturnsTheTrimmedWebsiteCode(): void
    {
        $this->assertSame('abc', (string) Website::fromString(' abc '));
    }

    public function testTwoWebsitesWithDifferentCodesAreNotEqual(): void
    {
        $websiteOne = Website::fromString('one');
        $websiteTwo = Website::fromString('two');
        $this->assertFalse($websiteOne->isEqual($websiteTwo));
    }

    public function testTwoWebsitesWithSameCodesAreEqual(): void
    {
        $websiteOne = Website::fromString('test');
        $websiteTwo = Website::fromString('test');
        $this->assertTrue($websiteOne->isEqual($websiteTwo));
    }

    public function testAWebsiteInstanceCanBeUsedAsTheInput(): void
    {
        $input = Website::fromString('test');
        $output = Website::fromString($input);
        $this->assertTrue($input->isEqual($output));
    }
}
