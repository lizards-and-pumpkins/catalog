<?php


namespace Brera\Context;

use Brera\DataVersion;

/**
 * @covers \Brera\Context\InternalContextState
 * @uses   \Brera\DataVersion
 */
class InternalContextStateTest extends \PHPUnit_Framework_TestCase
{
    private $testContextDataSet = ['language' => 'en_US'];

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->testDataVersion = DataVersion::fromVersionString(2.0);
    }

    /**
     * @return InternalContextState
     */
    private function createInternalContextStateInstance()
    {
        return InternalContextState::fromContextFields($this->testDataVersion, $this->testContextDataSet);
    }

    /**
     * @test
     */
    public function itShouldImplementContextState()
    {
        $this->assertInstanceOf(ContextState::class, $this->createInternalContextStateInstance());
    }

    /**
     * @test
     */
    public function itShouldReturnTheContextDataSet()
    {
        $state = $this->createInternalContextStateInstance();
        $this->assertSame($this->testContextDataSet, $state->getContextDataSet());
    }

    /**
     * @test
     */
    public function itShouldReturnTheDataVersion()
    {
        $state = $this->createInternalContextStateInstance();
        $this->assertSame((string)$this->testDataVersion, $state->getVersion());
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfStateRepresentationIsNotAString()
    {
        InternalContextState::fromStringRepresentation(null);
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfStateRepresentationIsNotValidJson()
    {
        InternalContextState::fromStringRepresentation('invalid-json');
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheDataVersionIsMissing()
    {
        $missingDataVersion = json_encode([]);
        InternalContextState::fromStringRepresentation($missingDataVersion);
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheContextDataSetIsMissing()
    {
        $missingContextDataSet = json_encode(['data_version' => 11]);
        InternalContextState::fromStringRepresentation($missingContextDataSet);
    }

    /**
     * @test
     * @expectedException \Brera\Context\InvalidContextStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheContextDataSetIsNotAnArray()
    {
        $missingContextDataSet = json_encode(['data_version' => 11, 'context_set' => 'no-array']);
        InternalContextState::fromStringRepresentation($missingContextDataSet);
    }

    /**
     * @test
     */
    public function itShouldBeInstantiableFromStringRepresentation()
    {
        $stringStateRepresentation = json_encode(['data_version' => 11, 'context_set' => []]);
        $state = InternalContextState::fromStringRepresentation($stringStateRepresentation);
        $this->assertInstanceOf(ContextState::class, $state);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheVersionInTheStringRepresentation()
    {
        $state = $this->createInternalContextStateInstance();
        $data = json_decode($state->getStringRepresentation(), true);
        $this->assertArrayHasKey('data_version', $data);
        $this->assertEquals((string)$this->testDataVersion, $data['data_version']);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheContextDataSetInTheStringRepresentation()
    {
        $state = $this->createInternalContextStateInstance();
        $data = json_decode($state->getStringRepresentation(), true);
        $this->assertArrayHasKey('context_set', $data);
        $this->assertEquals($this->testContextDataSet, $data['context_set']);
    }

    /**
     * @test
     */
    public function theOriginalStateShouldBeTheSameAsTheRehydratedState()
    {
        $state = $this->createInternalContextStateInstance();
        $stateRepresentation = $state->getStringRepresentation();
        /** @var InternalContextState $rehydratedState */
        $rehydratedState = InternalContextState::fromStringRepresentation($stateRepresentation);
        $this->assertSame($state->getVersion(), $rehydratedState->getVersion());
    }
}
