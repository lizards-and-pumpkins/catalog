<?php

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\Locale\Exception\InvalidLocaleSpecificationException;

/**
 * @covers \LizardsAndPumpkins\Context\Locale\Locale
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateLocaleFromNonString()
    {
        $this->expectException(InvalidLocaleSpecificationException::class);
        $invalidLocaleCode = new \stdClass();
        Locale::fromCodeString($invalidLocaleCode);
    }

    public function testLocaleCanBeConvertedToString()
    {
        $localeCode = 'foo_BAR';
        $locale = Locale::fromCodeString($localeCode);
        $this->assertSame($localeCode, (string) $locale);
    }
}
