<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\RootElementOfLayoutMustBeAnArrayException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\Layout
 */
class LayoutTest extends TestCase
{
    public function testLayoutIsCreatedFromArray(): void
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

    public function testAttributeValueIsReturned(): void
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

    public function testNullIsReturnedIfLayoutAttributeIsNotSet(): void
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

    public function testExceptionIsThrownIfRootElementIsNotAnArray(): void
    {
        $this->expectException(RootElementOfLayoutMustBeAnArrayException::class);
        Layout::fromArray(['foo']);
    }

    public function testFalseIsReturnedIfThereAreNoChildren(): void
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

    public function testTrueIsReturnedIfThereAreChildren(): void
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
