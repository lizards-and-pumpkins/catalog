<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

/**
 * @covers   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class SnippetCodeValidatorTest extends \PHPUnit_Framework_TestCase
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
