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

    /**
     * @return null
     */
    protected function setUp()
    {
        $this->testedBody = 'my cool body';
        $this->page = new Page($this->testedBody);
    }

    /**
     * @test
     */
    public function itShouldReturnASetBody()
    {
        $this->assertEquals($this->testedBody, $this->page->getBody());
    }

    /**
     * @test
     */
    public function itShouldEchoTheBodyOnSend()
    {
        ob_start();
        $this->page->send();
        $buffer = ob_get_clean();
        $this->assertEquals($this->testedBody, $buffer);
    }

    /**
     * @test
     * @dataProvider noStringProvider
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowAnExceptionIfNoString($noString)
    {
        new Page($noString);
    }

    public function noStringProvider()
    {
        return [
            array(
                new \stdClass()
            ),
            array(
                123
            ),
            array(
                1.01
            ),
            array(
                []
            ),
            array(
                true
            ),
            array(
                false
            )
        ];
    }
}
