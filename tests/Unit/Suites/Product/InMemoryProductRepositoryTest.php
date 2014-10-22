<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\InMemoryProductRepository
 * @uses \Brera\PoC\Product\Product
 */
class InMemoryProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryProductRepository
     */
    private $repository;
    
    public function setUp()
    {
        $this->repository = new InMemoryProductRepository();
    }

    /**
     * @test
     */
    public function itShouldBePossibleToCreateAProduct()
    {
        $testName = 'test';
        $stubProductId = $this->getStubProductId();
        $result = $this->repository->createProduct($stubProductId, $testName);
        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame($stubProductId, $result->getId());
        $this->assertSame($testName, $result->getName());
    }

    /**
     * @test
     */
    public function itShouldAddANewProductToTheRepository()
    {
        $testName = 'test';
        $stubProductId = $this->getStubProductId();
        $product = $this->repository->createProduct($stubProductId, $testName);
        $this->assertSame($product, $this->repository->findById($stubProductId));
    }

    /**
     * @test
     * @expectedException \Brera\PoC\Product\ProductNotFoundException
     */
    public function itShouldThrowAnExceptionIfAProductCantBeFound()
    {
        $stubProductId = $this->getStubProductId();
        $this->repository->findById($stubProductId);
    }

	/**
	 * @test
	 */
	public function itShouldReturnNull()
	{
		$result = $this->repository->commit();
		$this->assertNull($result);
	}
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProductId()
    {
        $stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $stubProductId;
    }
} 
