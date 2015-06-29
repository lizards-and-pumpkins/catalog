<?php

namespace Brera;

/**
 * @covers \Brera\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testedBody;
    /**
     * @var Page
     */
    private $page;

    protected function setUp()
    {
        $this->testedBody = 'my cool body';
        $this->page = new Page($this->testedBody);
    }

    public function testBodyIsReturned()
    {
        $this->assertEquals($this->testedBody, $this->page->getBody());
    }

    public function testBodyIsEchoed()
    {
        ob_start();
        $this->page->send();
        $buffer = ob_get_clean();

        $this->assertEquals($this->testedBody, $buffer);
    }

    /**
     * @dataProvider noStringProvider
     * @param mixed $noString
     */
    public function testExceptionIsThrownIfNoString($noString)
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new Page($noString);
    }

    /**
     * @return mixed[]
     */
    public function noStringProvider()
    {
        return [
            [new \stdClass()],
            [123],
            [1.01],
            [[]],
            [true],
            [false]
        ];
    }
}
