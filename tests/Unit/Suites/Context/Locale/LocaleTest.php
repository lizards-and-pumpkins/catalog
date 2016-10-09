<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Locale;

/**
 * @covers \LizardsAndPumpkins\Context\Locale\Locale
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    public function testLocaleCanBeConvertedToString()
    {
        $localeCode = 'foo_BAR';
        $locale = new Locale($localeCode);
        $this->assertSame($localeCode, (string) $locale);
    }
}
