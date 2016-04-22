<?php

namespace LizardsAndPumpkins\Unit\Suites\Util;


use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class SnippetCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsStringValidation()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $noString = 0;

        SnippetCodeValidator::validate($noString);
    }

    public function testNotEmptyValidation()
    {
        $this->expectException(InvalidSnippetCodeException::class);

        SnippetCodeValidator::validate('');
    }
}
