<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\Image\Exception\ProductImageListNotMutableException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 */
class ProductImageListTest extends TestCase
{
    /**
     * @param int $numberOfImages
     * @return ProductImage[]
     */
    private function createArrayOfStubImagesWithSize(int $numberOfImages) : array
    {
        if (0 === $numberOfImages) {
            return [];
        }
        return array_map(function () {
            return $this->createMock(ProductImage::class);
        }, range(1, $numberOfImages));
    }

    public function testItImplementsTheCountableInterface(): void
    {
        $this->assertInstanceOf(\Countable::class, new ProductImageList());
    }

    /**
     * @dataProvider numberOfImagesProvider
     * @param int $numberOfImages
     */
    public function testItReturnsTheCorrectNumberOfImages(int $numberOfImages): void
    {
        $stubImages = $this->createArrayOfStubImagesWithSize($numberOfImages);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertCount($numberOfImages, $imageList);
    }

    /**
     * @return array[]
     */
    public function numberOfImagesProvider() : array
    {
        return [[0], [1], [2], [3]];
    }

    public function testItReturnsTheImagesArray(): void
    {
        $stubImages = $this->createArrayOfStubImagesWithSize(2);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertIsArray($imageList->getImages());
        $this->assertCount(2, $imageList->getImages());
        $this->assertContainsOnlyInstancesOf(ProductImage::class, $imageList->getImages());
    }

    public function testItImplementsIteratorAggregate(): void
    {
        $this->assertInstanceOf(\IteratorAggregate::class, new ProductImageList());
    }

    public function testItIteratesOverTheImages(): void
    {
        $stubImages = $this->createArrayOfStubImagesWithSize(2);
        $imageList = new ProductImageList(...$stubImages);
        $counter = 0;
        foreach ($imageList as $image) {
            $counter++;
            $this->assertInstanceOf(ProductImage::class, $image);
        }
        $this->assertSame(2, $counter);
    }

    public function testItImplementsArrayAccess(): void
    {
        $this->assertInstanceOf(\ArrayAccess::class, new ProductImageList());
    }

    public function testItReturnsIfAnOffsetExists(): void
    {
        $stubImages = $this->createArrayOfStubImagesWithSize(2);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertTrue(isset($imageList[0]));
        $this->assertTrue(isset($imageList[1]));
        $this->assertFalse(isset($imageList[2]));
    }

    public function testItReturnsTheImageByOffset(): void
    {
        $stubImage = $this->createMock(ProductImage::class);
        $imageList = new ProductImageList($stubImage);
        $this->assertSame($stubImage, $imageList[0]);
    }

    public function testItThrowsAnExceptionIfAnOffsetIsSet(): void
    {
        $this->expectException(ProductImageListNotMutableException::class);
        $this->expectExceptionMessage('ProductImageList instances are immutable');
        $imageList = new ProductImageList($this->createMock(ProductImage::class));
        $imageList[0] = 123;
    }

    public function testItThrowsAnExceptionIfAnOffsetIsUnset(): void
    {
        $this->expectException(ProductImageListNotMutableException::class);
        $this->expectExceptionMessage('ProductImageList instances are immutable');
        $imageList = new ProductImageList($this->createMock(ProductImage::class));
        unset($imageList[0]);
    }

    public function testItImplementsJsonSerializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductImageList());
    }

    public function testItCanBeJsonEncodedAndRehydrated(): void
    {
        $productImage = new ProductImage(ProductAttributeList::fromArray([]));
        $sourceProductImageList = new ProductImageList($productImage);
        
        $json = json_encode($sourceProductImageList);
        $rehydratedProductImageList = ProductImageList::fromImages(...json_decode($json, true));
        
        $this->assertSame(count($sourceProductImageList), count($rehydratedProductImageList));
    }
}
