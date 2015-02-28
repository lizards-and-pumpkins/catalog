<?php

namespace Brera;

class PageMetaInfoSnippetContent
{
    const KEY_SOURCE_ID = 'source_id';
    const KEY_ROOT_SNIPPET_CODE = 'root_snippet_code';
    const KEY_PAGE_SNIPPET_CODES = 'page_snippet_codes';

    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var array
     */
    private $pageSnippetCodes;

    /**
     * @param string $sourceId
     * @param string $rootSnippetCode
     * @param array $pageSnippetCodes
     */
    private function __construct($sourceId, $rootSnippetCode, array $pageSnippetCodes)
    {
        $this->sourceId = $sourceId;
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
    }

    /**
     * @param string $sourceId
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return PageMetaInfoSnippetContent
     */
    public static function create($sourceId, $rootSnippetCode, array $pageSnippetCodes)
    {
        self::validateSourceId($sourceId);
        self::validateRootSnippetCode($rootSnippetCode);
        if (!in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }
        return new self($sourceId, $rootSnippetCode, $pageSnippetCodes);
    }

    /**
     * @param string $json
     * @return PageMetaInfoSnippetContent
     */
    public static function fromJson($json)
    {
        $pageInfo = self::decodeJson($json);
        self::validateRequiredKeysArePresent($pageInfo);
        return static::create(
            $pageInfo[self::KEY_SOURCE_ID],
            $pageInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageInfo[self::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @param $sourceId
     * @return string
     */
    private static function getNonScalarTypeRepresentation($sourceId)
    {
        return is_object($sourceId) ?
            get_class($sourceId) :
            gettype($sourceId);
    }

    /**
     * @param mixed[] $pageInfo
     */
    protected static function validateRequiredKeysArePresent(array $pageInfo)
    {
        foreach ([self::KEY_SOURCE_ID, self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES] as $key) {
            if (!array_key_exists($key, $pageInfo)) {
                throw new \RuntimeException(sprintf('Missing key in input JSON: "%s"', $key));
            }
        }
    }

    /**
     * @param string $json
     * @return mixed[]
     * @throws \OutOfBoundsException
     */
    private static function decodeJson($json)
    {
        $result = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf(
                'JSON decode error: %s',
                json_last_error_msg()
            ));
        }
        return $result;
    }

    /**
     * @return mixed[]
     */
    public function getInfo()
    {
        return [
            self::KEY_SOURCE_ID => $this->sourceId,
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes
        ];
    }

    /**
     * @param mixed $sourceId
     */
    private static function validateSourceId($sourceId)
    {
        if (!is_scalar($sourceId)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info source id has to be a scalar value, got "%s"',
                self::getNonScalarTypeRepresentation($sourceId)
            ));
        }
    }

    /**
     * @param mixed $rootSnippetCode
     */
    private static function validateRootSnippetCode($rootSnippetCode)
    {
        if (! is_string($rootSnippetCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info root snippet code has to be a string value, got "%s"',
                gettype($rootSnippetCode)
            ));
        }
    }

    /**
     * @return string
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @return string
     */
    public function getRootSnippetCode()
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes()
    {
        return $this->pageSnippetCodes;
    }
}
