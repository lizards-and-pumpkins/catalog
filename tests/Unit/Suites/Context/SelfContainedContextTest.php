<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Exception\ContextCodeNotFoundException;

class SelfContainedContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $data
     * @return SelfContainedContext
     */
    private function createContext(array $data)
    {
        return SelfContainedContext::fromArray($data);
    }
    
    public function testItImplementsTheContextInterface()
    {
        $this->assertInstanceOf(Context::class, $this->createContext([]));
    }

    public function testItReturnsAStringRepresentationContainingTheDataParts()
    {
        $this->assertSame('key1:value1', (string) $this->createContext(['key1' => 'value1']));
        $this->assertSame(
            'key1:value1_key2:value2',
            (string) $this->createContext(['key1' => 'value1', 'key2' => 'value2'])
        );
    }

    public function testItExtractsTheGivenParts()
    {
        $this->assertSame('key1:value1', (string) $this->createContext(['key1' => 'value1'])->getIdForParts(['key1']));
        $this->assertSame(
            'key2:value2',
            (string) $this->createContext(['key1' => 'value1', 'key2' => 'value2'])->getIdForParts(['key2'])
        );
        $contextData = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3', 'key4' => 'value4'];
        $this->assertSame(
            'key2:value2_key4:value4',
            (string) $this->createContext($contextData)->getIdForParts(['key2', 'key4'])
        );
    }

    public function testItThrowsAnExceptionIfTheRequestedPartIsNotPresent()
    {
        $this->setExpectedException(
            ContextCodeNotFoundException::class,
            'No value found in the current context for the code "test"'
        );
        $this->createContext([])->getValue('test');
    }

    public function testItReturnsTheValueForTheGivenCode()
    {
        $this->assertSame('value', $this->createContext(['key' => 'value'])->getValue('key'));
        $this->assertSame('value2', $this->createContext(['key1' => 'value1', 'key2' => 'value2'])->getValue('key2'));
    }

    public function testItReturnsTheSupportedContextPartCodes()
    {
        $this->assertSame([], $this->createContext([])->getSupportedCodes());
        $this->assertSame(['key'], $this->createContext(['key' => 'value'])->getSupportedCodes());
    }

    public function testItReturnsFalseIfTheGivenContextPartCodeIsNotPresent()
    {
        $this->assertFalse($this->createContext([])->supportsCode('key'));
    }

    public function testItReturnsTrueIfTheGivenContextPartCodeIsPresent()
    {
        $this->assertTrue($this->createContext(['key' => 'value'])->supportsCode('key'));
        $this->assertTrue($this->createContext(['key' => '0'])->supportsCode('key'));
        $this->assertTrue($this->createContext(['key' => ''])->supportsCode('key'));
    }

    public function testItIsNotASubsetIfTheGivenContextHasCodesThatWeDoNotHave()
    {
        $contextA = $this->createContext(['key1' => 'value1']);
        $contextB = $this->createContext(['key2' => 'value2']);
        
        $this->assertFalse($contextB->isSubsetOf($contextA));
    }

    public function testItIsNotASubsetIfTheGivenContextHasCodesThatWeHaveButTheValueIsDifferent()
    {
        $contextA = $this->createContext(['key1' => 'value1']);
        $contextB = $this->createContext(['key1' => 'value2']);
        
        $this->assertFalse($contextB->isSubsetOf($contextA));
    }

    public function testItIsASubsetIfTheGivenContextHasOnlyCodesThatWeAlsoHaveAndTheValuesAreTheSame()
    {
        $contextA = $this->createContext(['key1' => 'value1']);
        $contextB = $this->createContext(['key1' => 'value1']);
        
        $this->assertTrue($contextB->isSubsetOf($contextA));
    }

    public function testItIsASubsetIfTheGivenContextHasMorePartsButTheSamePartsMatch()
    {
        $contextA = $this->createContext(['key1' => 'value1', 'key2' => 'value2']);
        $contextB = $this->createContext(['key1' => 'value1']);

        $this->assertTrue($contextB->isSubsetOf($contextA));
    }

    public function testItIsNotASubsetIfTheGivenContextLessParts()
    {
        $contextA = $this->createContext(['key1' => 'value1']);
        $contextB = $this->createContext(['key1' => 'value1', 'key2' => 'value2']);

        $this->assertFalse($contextB->isSubsetOf($contextA));
    }

    /**
     * @param string[] $contextDataSet
     * @param string[] $matchingDataSet
     * @dataProvider matchingDataSetProvider
     */
    public function testItMatchesDataSetsWhereAllSharedPartsHaveTheSameValue($contextDataSet, $matchingDataSet)
    {
        $this->assertTrue($this->createContext($contextDataSet)->matchesDataSet($matchingDataSet));
    }

    /**
     * @return array[]
     */
    public function matchingDataSetProvider()
    {
        return [
            [[], []],
            [['key1' => 'value1'], []],
            [[], ['key2' => 'value2']],
            [['key1' => 'value1'], ['key2' => 'value2']],
            [['key1' => 'value1', 'key2' => 'value2'], ['key1' => 'value1']],
        ];
    }

    /**
     * @dataProvider nonMatchingDataSetProvider
     * @param string[] $contextDataSet
     * @param string[] $nonMatchingSet
     */
    public function testItDoesNotMatchADataSetWhereTheValueOfACommonPartIsDifferent($contextDataSet, $nonMatchingSet)
    {
        $this->assertFalse($this->createContext($contextDataSet)->matchesDataSet($nonMatchingSet));
    }

    /**
     * @return array[]
     */
    public function nonMatchingDataSetProvider()
    {
        return [
            [['key1' => 'value1'], ['key1' => 'XXX']],
            [['key1' => 'value1', 'key2' => 'value2'], ['key1' => 'value1', 'key2' => 'XXX']],
        ];
    }

    public function testItReturnsTheContextPartsArrayToJsonSerialize()
    {
        $contextParts = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertSame($contextParts, $this->createContext($contextParts)->jsonSerialize());
    }
}
