<?php


namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Context\ContextState;
use Brera\Context\InternalContextState;

class InternalSearchDocumentState implements SearchDocumentState
{
    /**
     * @var string[]
     */
    private $fields;

    /**
     * @var ContextState
     */
    private $contextState;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string[] $fields
     * @param ContextState $contextState
     * @param string $content
     */
    private function __construct(array $fields, ContextState $contextState, $content)
    {
        $this->fields = $fields;
        $this->contextState = $contextState;
        $this->content = $content;
    }

    /**
     * @param string $content
     * @param SearchDocumentFieldCollection $fields
     * @param Context $context
     * @return SearchDocumentState
     * @throws InvalidSearchDocumentContentException
     */
    public static function fromSearchDocumentFields($content, SearchDocumentFieldCollection $fields, Context $context)
    {
        self::validateContent($content);
        return new self($fields->toArray(), $context->getState(), $content);
    }

    /**
     * @param string $serializedStateString
     * @return SearchDocumentState
     * @throws InvalidSearchDocumentStateRepresentationException
     */
    public static function fromStringRepresentation($serializedStateString)
    {
        $data = self::getDecodedStringRepresentation($serializedStateString);
        $fields = self::getFieldDataFromDecodedRepresentation($data);
        $contextState = self::getContextStateFromDecodedRepresentation($data);
        $content = self::getContentFromDecodedRepresentation($data);
        return new self($fields, $contextState, $content);
    }

    /**
     * @param string $serializedStateString
     * @return mixed
     */
    private static function getDecodedStringRepresentation($serializedStateString)
    {
        $data = json_decode($serializedStateString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidSearchDocumentStateRepresentationException(sprintf(
                'Unable to unserialize the SearchDocument string representation'
            ));
        }
        return $data;
    }

    /**
     * @param string $content
     */
    private static function validateContent($content)
    {
        if (!is_string($content)) {
            throw new InvalidSearchDocumentContentException(sprintf(
                'The search engine content has to be specified as a string, got "%s"',
                (is_object($content) ? get_class($content) : gettype($content))
            ));
        }
    }

    /**
     * @param string[] $data
     * @return string[]
     */
    private static function getFieldDataFromDecodedRepresentation(array $data)
    {
        if (!array_key_exists('fields', $data) || !is_array($data['fields'])) {
            throw new InvalidSearchDocumentStateRepresentationException(sprintf(
                'The SearchDocumentState is missing the field list'
            ));
        }
        return $data['fields'];
    }

    /**
     * @param string[] $data
     * @return string
     */
    private static function getContentFromDecodedRepresentation($data)
    {
        if (!array_key_exists('content', $data)) {
            throw new InvalidSearchDocumentStateRepresentationException(sprintf(
                'The SearchDocumentState is missing the content list'
            ));
        }
        self::validateContent($data['content']);
        return $data['content'];
    }

    /**
     * @param string[] $data
     * @return ContextState
     */
    private static function getContextStateFromDecodedRepresentation($data)
    {
        if (!array_key_exists('context_state', $data)) {
            throw new InvalidSearchDocumentStateRepresentationException(sprintf(
                'The SearchDocumentState is missing the context state'
            ));
        }
        return InternalContextState::fromStringRepresentation($data['context_state']);
    }

    /**
     * @return string
     */
    public function getStringRepresentation()
    {
        return json_encode([
            'content' => $this->content,
            'context_state' => $this->contextState->getStringRepresentation(),
            'fields' => $this->fields
        ]);
    }

    /**
     * @return SearchDocumentFieldCollection
     */
    public function getFields()
    {
        return SearchDocumentFieldCollection::fromArray($this->fields);
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return ContextBuilder::getContextFromMemento($this->contextState);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
