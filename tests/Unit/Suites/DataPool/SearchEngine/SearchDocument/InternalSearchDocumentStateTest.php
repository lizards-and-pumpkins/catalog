<?php


namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\DataVersion;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\InternalSearchDocumentState
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\InternalContextState
 * @uses   \Brera\Context\ContextBuilder
 */
class InternalSearchDocumentStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFields;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testContext;

    /**
     * @var string
     */
    private $testContent = 'test-content';

    /**
     * @return InternalSearchDocumentState
     */
    private function createSearchDocumentStateInstance()
    {
        return InternalSearchDocumentState::fromSearchDocumentFields(
            $this->testContent,
            $this->mockFields,
            $this->testContext
        );
    }

    protected function setUp()
    {
        $this->mockFields = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $this->mockFields->expects($this->any())->method('toArray')->willReturn([]);
        $this->testContext = new VersionedContext(DataVersion::fromVersionString('1.2'));
    }

    /**
     * @test
     */
    public function itShouldTakeAndReturnSearchDocumentContent()
    {
        $instance = $this->createSearchDocumentStateInstance();
        $this->assertInstanceOf(InternalSearchDocumentState::class, $instance);
        $this->assertInstanceOf(SearchDocumentState::class, $instance);
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentContentException
     */
    public function itShouldThrowAnExceptionIfTheContentIsNoString()
    {
        $invalidContent = 123;
        InternalSearchDocumentState::fromSearchDocumentFields($invalidContent, $this->mockFields, $this->testContext);
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentStateRepresentationException
     */
    public function itShouldItShouldThrowAnExceptionInTheJsonCantBeDecoded()
    {
        InternalSearchDocumentState::fromStringRepresentation('invalid-json');
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheFieldsAreMissing()
    {
        $missingFields = json_encode([]);
        InternalSearchDocumentState::fromStringRepresentation($missingFields);
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheContextStateIsMissing()
    {
        $missingContextState = json_encode([
            'fields' => []
        ]);
        InternalSearchDocumentState::fromStringRepresentation($missingContextState);
    }

    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\SearchDocument\InvalidSearchDocumentStateRepresentationException
     */
    public function itShouldThrowAnExceptionIfTheContentIsMissing()
    {
        $missingContent = json_encode([
            'fields' => [],
            'context_state' => json_encode(['data_version' => '123', 'context_set' => []]),
        ]);
        InternalSearchDocumentState::fromStringRepresentation($missingContent);
    }

    /**
     * @test
     */
    public function itShouldReturnASearchDocumentStateInstanceFromStringRepresentation()
    {
        $completeContent = json_encode([
            'fields' => [],
            'context_state' => json_encode(['data_version' => '123', 'context_set' => []]),
            'content' => 'test-content'
        ]);
        $result = InternalSearchDocumentState::fromStringRepresentation($completeContent);
        $this->assertInstanceOf(SearchDocumentState::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnJsonContainingAllProperties()
    {
        $instance = $this->createSearchDocumentStateInstance();
        $stringRepresentation = $instance->getStringRepresentation();
        $result = json_decode($stringRepresentation, true);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertSame($this->testContent, $result['content']);
        $this->assertArrayHasKey('context_state', $result);
        $this->assertInternalType('string', $result['context_state']);
        $this->assertArrayHasKey('fields', $result);
        $this->assertInternalType('array', $result['fields']);
        
    }

    /**
     * @test
     */
    public function itShouldReturnSearchDocumentFieldCollection()
    {
        $instance = $this->createSearchDocumentStateInstance();
        $this->assertInstanceOf(SearchDocumentFieldCollection::class, $instance->getFields());
    }

    /**
     * @test
     */
    public function itShouldReturnContext()
    {
        $instance = $this->createSearchDocumentStateInstance();
        $this->assertInstanceOf(Context::class, $instance->getContext());
    }

    /**
     * @test
     */
    public function itShouldReturnTheContent()
    {
        $this->assertSame($this->testContent, $this->createSearchDocumentStateInstance()->getContent());
    }
}
