<?php

namespace Brera;

use Brera\KeyValue\DataPoolWriter;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Product\ProductSourceBuilder;

/**
 * @covers \Brera\CommonFactory
 * @uses   \Brera\KeyValue\DataPoolWriter
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\DomainEventHandlerLocator
 */
class CommonFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommonFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new CommonFactory();
    }

    /**
     * @test
     */
    public function itShouldCreateSnippetResultList()
    {
        $result = $this->factory->createSnippetResultList();

        $this->assertInstanceOf(SnippetResultList::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateSnippetKeyGenerator()
    {
        $result = $this->factory->createProductDetailViewSnippetKeyGenerator();

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductBuilder()
    {
        $result = $this->factory->createProductSourceBuilder();

        $this->assertInstanceOf(ProductSourceBuilder::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnThemeLocator()
    {
        $result = $this->factory->createThemeLocator();

        $this->assertInstanceOf(ThemeLocator::class, $result);
    }
}
