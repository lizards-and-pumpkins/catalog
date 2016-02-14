<?php


namespace LizardsAndPumpkins\Product\ProductImage;

use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage\Exception\ProductImageListNotMutableException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductImage\ProductImageList
 * @uses   \LizardsAndPumpkins\Product\ProductImage\ProductImage
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class ProductImageListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $numberOfImages
     * @return ProductImage[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    private function createArrayOfStubImagesWithSize($numberOfImages)
    {
        if (0 === $numberOfImages) {
            return [];
        }
        return array_map(function () {
            return $this->getMock(ProductImage::class, [], [], '', false);
        }, range(1, $numberOfImages));
    }

    public function testItImplementsTheCountableInterface()
    {
        $this->assertInstanceOf(\Countable::class, new ProductImageList());
    }

    /**
     * @param int $numberOfImages
     * @dataProvider numberOfImagesProvider
     */
    public function testItReturnsTheCorrectNumberOfImages($numberOfImages)
    {
        $stubImages = $this->createArrayOfStubImagesWithSize($numberOfImages);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertCount($numberOfImages, $imageList);
    }

    /**
     * @return array[]
     */
    public function numberOfImagesProvider()
    {
        return [[0], [1], [2], [3]];
    }

    public function testItReturnsTheImagesArray()
    {
        $stubImages = $this->createArrayOfStubImagesWithSize(2);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertInternalType('array', $imageList->getImages());
        $this->assertCount(2, $imageList->getImages());
        $this->assertContainsOnlyInstancesOf(ProductImage::class, $imageList->getImages());
    }

    public function testItImplementsIteratorAggregate()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, new ProductImageList());
    }

    public function testItIteratesOverTheImages()
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

    public function testItImplementsArrayAccess()
    {
        $this->assertInstanceOf(\ArrayAccess::class, new ProductImageList());
    }

    public function testItReturnsIfAnOffsetExists()
    {
        $stubImages = $this->createArrayOfStubImagesWithSize(2);
        $imageList = new ProductImageList(...$stubImages);
        $this->assertTrue(isset($imageList[0]));
        $this->assertTrue(isset($imageList[1]));
        $this->assertFalse(isset($imageList[2]));
    }

    public function testItReturnsTheImageByOffset()
    {
        $stubImage = $this->getMock(ProductImage::class, [], [], '', false);
        $imageList = new ProductImageList($stubImage);
        $this->assertSame($stubImage, $imageList[0]);
    }

    public function testItThrowsAnExceptionIfAnOffsetIsSet()
    {
        $this->expectException(ProductImageListNotMutableException::class);
        $this->expectExceptionMessage('ProductImageList instances are immutable');
        $imageList = new ProductImageList($this->getMock(ProductImage::class, [], [], '', false));
        $imageList[0] = 123;
    }

    public function testItThrowsAnExceptionIfAnOffsetIsUnset()
    {
        $this->expectException(ProductImageListNotMutableException::class);
        $this->expectExceptionMessage('ProductImageList instances are immutable');
        $imageList = new ProductImageList($this->getMock(ProductImage::class, [], [], '', false));
        unset($imageList[0]);
    }

    public function testItImplementsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, new ProductImageList());
    }

    public function testItCanBeJsonEncodedAndRehydrated()
    {
        $productImage = new ProductImage(ProductAttributeList::fromArray([]));
        $sourceProductImageList = new ProductImageList($productImage);
        
        $json = json_encode($sourceProductImageList);
        $rehydratedProductImageList = ProductImageList::fromArray(json_decode($json, true));
        
        $this->assertSame(count($sourceProductImageList), count($rehydratedProductImageList));
    }
}
