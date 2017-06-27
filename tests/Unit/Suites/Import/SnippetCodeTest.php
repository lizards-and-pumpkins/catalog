<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \LizardsAndPumpkins\Import\SnippetCode
 */
class SnippetCodeTest extends TestCase
{
    public function testExceptionIsThrownIfSnippetCodeIsNonString()
    {
        $this->expectException(\TypeError::class);
        new SnippetCode(123);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testThrowsAnExceptionIfSnippetCodeIsAnEmptyString(string $emptyString)
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('Snippet code must not be empty.');

        new SnippetCode($emptyString);
    }

    public function testThrowsAnExceptionIfSnippetCodeIsShorterThanTwoCharacters()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('The snippet container code has to be at least 2 characters long.');

        new SnippetCode(' a');
    }

    public function testReturnsTrimmedSnippetCodeString()
    {
        $this->assertSame('foo', (string) new SnippetCode(' foo '));
    }

    public function testReturnsJsonRepresentationOfSnippetCode()
    {
        $this->assertSame('"foo"', json_encode(new SnippetCode('foo')));
    }

    public function emptyStringProvider(): array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }
}
