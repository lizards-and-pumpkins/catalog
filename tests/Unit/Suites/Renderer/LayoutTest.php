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
            'name'          => 'snippet',
            'attributes'    => ['name' => 'foo'],
            'value'         => [[
                'name'          => 'block',
                'attributes'    => ['class' => 'bar', 'template' => 'baz'],
                'value'         => 'qux'
            ]]
        ]];

        $snippet = Layout::fromArray($layoutArray);

        $this->assertEquals('snippet', $snippet->getName());
        $this->assertEquals(['name' => 'foo'], $snippet->getAttributes());
        $this->assertContainsOnly(Layout::class, $snippet->getPayload());
    }

    /**
     * @test
     */
    public function itShouldReturnAnAttributeValue()
    {
        $layoutArray = [[
            'name'          => 'snippet',
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
            'name'          => 'foo',
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
