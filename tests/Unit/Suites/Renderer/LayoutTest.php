<?php

namespace Brera\Renderer;

/**
 * @covers \Brera\Renderer\Layout
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateLayoutFromArray()
    {
        $layoutArray = [[
            'nodeName'      => 'snippet',
            'attributes'    => ['name' => 'foo'],
            'value'         => [[
                'nodeName'      => 'block',
                'attributes'    => ['class' => 'bar', 'template' => 'baz'],
                'value'         => 'qux'
            ]]
        ]];

        $snippetLayout = Layout::fromArray($layoutArray);

        $this->assertEquals('snippet', $snippetLayout->getNodeName());
        $this->assertEquals(['name' => 'foo'], $snippetLayout->getAttributes());
        $this->assertContainsOnly(Layout::class, $snippetLayout->getNodeValue());
    }

    /**
     * @test
     */
    public function itShouldReturnAnAttributeValue()
    {
        $layoutArray = [[
            'nodeName'      => 'snippet',
            'attributes'    => ['name' => 'foo'],
            'value'         => 'bar'
        ]];

        $snippet = Layout::fromArray($layoutArray);

        $this->assertEquals('foo', $snippet->getAttribute('name'));
    }

    /**
     * @test
     */
    public function itShouldReturnNullIfLayoutAttributeIsNotSet()
    {
        $layoutArray = [[
            'nodeName'      => 'foo',
            'attributes'    => [],
            'value'         => 'bar'
        ]];

        $snippet = Layout::fromArray($layoutArray);

        $this->assertNull($snippet->getAttribute('name'));
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\RootElementOfLayoutMustBeAnArrayException
     */
    public function itShouldThrowAnExceptionIfRootElementIsNotAnArray()
    {
        Layout::fromArray(['foo']);
    }

}
