<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class SnippetCodeValidatorTest extends TestCase
{
    public function testExceptionIsThrownIfSnippetCodeIsNonString()
    {
        $this->expectException(\TypeError::class);
        SnippetCodeValidator::validate(123);
    }

    public function testExceptionIsThrownIfSnippetCodeIsAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        SnippetCodeValidator::validate('');
    }
}
