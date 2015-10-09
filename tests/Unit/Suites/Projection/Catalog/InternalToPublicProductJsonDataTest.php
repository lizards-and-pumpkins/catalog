<?php


namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\SimpleProduct;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData
 */
class InternalToPublicProductJsonDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $expectedProductJsonData
     * @param mixed $internalProductJsonData
     */
    public function assertPublicJson($expectedProductJsonData, $internalProductJsonData)
    {
        $toPublicJson = new InternalToPublicProductJsonData();
        $this->assertSame($expectedProductJsonData, $toPublicJson->transformProduct($internalProductJsonData));
    }

    public function testAnEmptyArrayInputReturnsEmptyArray()
    {
        $internalJsonData = [];
        $expectedData = [];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItRemovesTheContextFromProducts()
    {
        $internalJsonData = [
            'product_id' => 'test',
            SimpleProduct::CONTEXT => [],
        ];
        $expectedData = [
            'product_id' => 'test',
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItFlattensAttributes()
    {
        $internalJsonData = [
            'product_id' => 'test',
            'attributes' => [
                [
                    ProductAttribute::CODE => 'foo',
                    ProductAttribute::CONTEXT => [],
                    ProductAttribute::VALUE => 'bar'
                ],
            ],
        ];
        $expectedData = [
            'product_id' => 'test',
            'attributes' => [
                'foo' => 'bar'
            ],
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItCombinesTheValuesOfAttributesWithTheSameCodeIntoArrays()
    {
        $internalJsonData = [
            'product_id' => 'test',
            'attributes' => [
                [
                    ProductAttribute::CODE => 'foo',
                    ProductAttribute::CONTEXT => [],
                    ProductAttribute::VALUE => 'bar'
                ],
                [
                    ProductAttribute::CODE => 'foo',
                    ProductAttribute::CONTEXT => [],
                    ProductAttribute::VALUE => 'buz'
                ],
                [
                    ProductAttribute::CODE => 'foo',
                    ProductAttribute::CONTEXT => [],
                    ProductAttribute::VALUE => 'qux'
                ],
            ],
        ];
        $expectedData = [
            'product_id' => 'test',
            'attributes' => [
                'foo' => ['bar', 'buz', 'qux']
            ],
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }
    
    public function testItFlattensImageAttributes()
    {

        $internalJsonData = [
            'product_id' => 'test',
            'images' => [
                [
                    [
                        ProductAttribute::CODE => 'file',
                        ProductAttribute::CONTEXT => [],
                        ProductAttribute::VALUE => 'foo.jpg'
                    ],
                    [
                        ProductAttribute::CODE => 'label',
                        ProductAttribute::CONTEXT => [],
                        ProductAttribute::VALUE => 'Image One'
                    ],
                ],
                [
                    [
                        ProductAttribute::CODE => 'file',
                        ProductAttribute::CONTEXT => [],
                        ProductAttribute::VALUE => 'bar.jpg'
                    ],
                    [
                        ProductAttribute::CODE => 'label',
                        ProductAttribute::CONTEXT => [],
                        ProductAttribute::VALUE => 'Image Two'
                    ],
                ],
            ],
        ];
        $expectedData = [
            'product_id' => 'test',
            'images' => [
                [
                    'file' => 'foo.jpg',
                    'label' => 'Image One'
                ],
                [
                    'file' => 'bar.jpg',
                    'label' => 'Image Two'
                ],
            ]
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItKeepsVariationAttributes()
    {
        $internalJsonData = [
            'product_id' => 'test',
            'variation_attributes' => ['foo', 'bar'],
        ];
        $expectedData = [
            'product_id' => 'test',
            'variation_attributes' => ['foo', 'bar']
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItFlattensTheSimpleProductIntoTheMainProduct()
    {
        $internalJsonData = [
            'simple_product' => [
                'product_id' => 'test',
                SimpleProduct::CONTEXT => [],
                'attributes' => [
                    [
                        ProductAttribute::CODE => 'foo',
                        ProductAttribute::CONTEXT => [],
                        ProductAttribute::VALUE => 'bar'
                    ],
                ],
                'images' => [],
            ],
            'variation_attributes' => ['foo'],
        ];
        $expectedData = [
            'product_id' => 'test',
            'attributes' => [
                'foo' => 'bar',
            ],
            'images' => [],
            'variation_attributes' => ['foo'],
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }

    public function testItFlattensAssociatedProducts()
    {
        $internalJsonData = [
            'product_id' => 'parent',
            'associated_products' => [
                'products' => [
                    [
                        'product_id' => 'child',
                        SimpleProduct::CONTEXT => [],
                        'attributes' => [
                            [
                                ProductAttribute::CODE => 'foo',
                                ProductAttribute::CONTEXT => [],
                                ProductAttribute::VALUE => 'bar'
                            ],
                        ],
                        'images' => [
                            [
                                [
                                    ProductAttribute::CODE => 'file',
                                    ProductAttribute::CONTEXT => [],
                                    ProductAttribute::VALUE => 'image.jpg'
                                ]
                            ]
                        ],
                    ],
                ],
                AssociatedProductList::PHP_CLASSES => [
                    SimpleProduct::class
                ]
            ],
        ];
        $expectedData = [
            'product_id' => 'parent',
            'associated_products' => [
                [
                    'product_id' => 'child',
                    'attributes' => [
                        'foo' => 'bar'
                    ],
                    'images' => [
                        [
                            'file' => 'image.jpg'
                        ]
                    ]
                ]
            ],
        ];
        $this->assertPublicJson($expectedData, $internalJsonData);
    }
}
