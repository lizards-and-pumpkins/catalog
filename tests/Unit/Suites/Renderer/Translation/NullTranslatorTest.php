<?php

namespace Brera\Renderer\Translation;

/**
 * @covers \Brera\Renderer\Translation\NullTranslator
 */
class NullTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullTranslator
     */
    private $translator;

    protected function setUp()
    {
        $this->translator = new NullTranslator;
    }

    public function testTranslatorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Translator::class, $this->translator);
    }

    public function testTranslationIsAlwaysIdenticalToOriginalString()
    {
        $testTranslationSource = 'foo';
        $result = $this->translator->translate($testTranslationSource);

        $this->assertSame($testTranslationSource, $result);
    }
}
