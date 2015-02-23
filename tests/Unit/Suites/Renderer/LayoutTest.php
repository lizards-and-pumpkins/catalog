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
        $layoutArray = [
            [
                'attributes' => ['name' => 'a-block'],
                'value' => [
                    [
                        'attributes' => ['class' => 'bar', 'template' => 'baz'],
                        'value' => 'a-child'
                    ]
                ]
            ]
        ];

        $snippetLayout = Layout::fromArray($layoutArray);

        $this->assertEquals(['name' => 'a-block'], $snippetLayout->getAttributes());
        $this->assertContainsOnly(Layout::class, $snippetLayout->getNodeChildren());
    }

    /**
     * @test
     */
    public function itShouldReturnAnAttributeValue()
    {
        $layoutArray = [
            [
                'attributes' => ['name' => 'foo'],
                'value' => 'bar'
            ]
        ];

        $snippet = Layout::fromArray($layoutArray);

        $this->assertEquals('foo', $snippet->getAttribute('name'));
    }

    /**
     * @test
     */
    public function itShouldReturnNullIfLayoutAttributeIsNotSet()
    {
        $layoutArray = [
            [
                'attributes' => [],
                'value' => 'bar'
            ]
        ];

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

    /**
     * @test
     */
    public function itShouldReturnFalseIfThereAreNoChildren()
    {
        $layoutArray = [
            [
                'attributes' => ['name' => 'a-block'],
                'value' => ''
            ]
        ];

        $layout = Layout::fromArray($layoutArray);
        $this->assertFalse($layout->hasChildren());
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfThereAreChildren()
    {
        $layoutArray = [
            [
                'attributes' => ['name' => 'a-block'],
                'value' => [
                    [
                        'attributes' => ['name' => 'a-child', 'class' => 'bar', 'template' => 'baz'],
                        'value' => ''
                    ]
                ]
            ]
        ];

        $layout = Layout::fromArray($layoutArray);
        $this->assertTrue($layout->hasChildren());
    }
}
