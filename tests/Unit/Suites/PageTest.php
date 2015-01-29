<?php

namespace Brera;

/**
 * @covers \Brera\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @return null
     */
    protected function setUp()
    {
        $this->page = new Page();
    }

    /**
     * @test
     */
    public function itShouldReturnASetBody()
    {
        $testedBody = 'my cool body';
        $this->page->setBody($testedBody);
        $this->assertEquals($testedBody, $this->page->getBody());
    }

    /**
     * @test
     */
    public function itShouldEchoTheBodyOnSend()
    {
        $testedBody = 'my cool body';
        $this->page->setBody($testedBody);
        ob_start();
        $this->page->send();
        $buffer = ob_get_clean();
        $this->assertEquals($testedBody, $buffer);
    }
}
