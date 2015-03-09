<?php

namespace Brera;

use Brera\Context\Context;

/**
 * @covers \Brera\RootSnippetSourceList
 * @uses   \Brera\RootSnippetSource
 */
class RootSnippetSourceListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnArrayOfRootSnippetSources()
    {
        $stubContext = $this->getMock(Context::class);
        $sourceDataPairs = [
            ['context' => $stubContext, 'numItemsPerPage' => 1]
        ];
        $rootSnippetSourceList = RootSnippetSourceList::fromArray($sourceDataPairs);

        $result = $rootSnippetSourceList->getSources();

        $this->assertContainsOnly(RootSnippetSource::class, $result);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidRootSnippetSourceDataException
     * @expectedExceptionMessage No valid context found in one or more root snippet source data pairs.
     */
    public function itShouldThrowAnExceptionIfDataDoesNotHaveAValidContext()
    {
        $stubContext = $this->getMock(Context::class);
        $sourceDataPairs = [
            ['context' => $stubContext, 'numItemsPerPage' => 1],
            ['context' => 1, 'numItemsPerPage' => 0]
        ];

        RootSnippetSourceList::fromArray($sourceDataPairs);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidRootSnippetSourceDataException
     * @expectedExceptionMessage No valid number of items per page found in one or more root snippet source data pairs.
     */
    public function itShouldThrowAnExceptionIfDataDoesNotHaveAValidNumberOfItemsPerPage()
    {
        $stubContext = $this->getMock(Context::class);
        $sourceDataPairs = [
            ['context' => $stubContext, 'numItemsPerPage' => 'a']
        ];

        RootSnippetSourceList::fromArray($sourceDataPairs);
    }
}
