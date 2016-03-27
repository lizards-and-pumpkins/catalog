<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\RootElementOfLayoutMustBeAnArrayException;
use LizardsAndPumpkins\Import\TemplateRendering\Layout;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\Layout
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    public function testLayoutIsCreatedFromArray()
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

    public function testAttributeValueIsReturned()
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

    public function testNullIsReturnedIfLayoutAttributeIsNotSet()
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

    public function testExceptionIsThrownIfRootElementIsNotAnArray()
    {
        $this->expectException(RootElementOfLayoutMustBeAnArrayException::class);
        Layout::fromArray(['foo']);
    }

    public function testFalseIsReturnedIfThereAreNoChildren()
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

    public function testTrueIsReturnedIfThereAreChildren()
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
