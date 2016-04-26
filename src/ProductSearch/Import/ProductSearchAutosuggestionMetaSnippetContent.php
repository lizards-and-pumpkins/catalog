<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetContainer;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class ProductSearchAutosuggestionMetaSnippetContent implements PageMetaInfoSnippetContent
{
    /**
     * @var string
     */
    private $rootSnippetCode;

    /**
     * @var string[]
     */
    private $pageSnippetCodes;

    /**
     * @var SnippetContainer[]
     */
    private $snippetContainers;

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $snippetContainers
     */
    private function __construct($rootSnippetCode, array $pageSnippetCodes, array $snippetContainers)
    {
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->snippetContainers = $snippetContainers;
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $snippetContainerData
     * @return ProductSearchAutosuggestionMetaSnippetContent
     */
    public static function create($rootSnippetCode, array $pageSnippetCodes, array $snippetContainerData)
    {
        SnippetCodeValidator::validate($rootSnippetCode);

        if (!in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }

        return new self($rootSnippetCode, $pageSnippetCodes, self::createSnippetContainers($snippetContainerData));
    }

    /**
     * @param array[] $containerArray
     * @return SnippetContainer[]
     */
    private static function createSnippetContainers(array $containerArray)
    {
        return array_map(function ($code) use ($containerArray) {
            return new SnippetContainer($code, $containerArray[$code]);
        }, array_keys($containerArray));
    }

    /**
     * @param string $json
     * @return ProductSearchAutosuggestionMetaSnippetContent
     */
    public static function fromJson($json)
    {
        $pageMetaInfo = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \OutOfBoundsException(sprintf('JSON decode error: %s', json_last_error_msg()));
        }

        foreach ([self::KEY_ROOT_SNIPPET_CODE, self::KEY_PAGE_SNIPPET_CODES, self::KEY_CONTAINER_SNIPPETS] as $key) {
            if (!array_key_exists($key, $pageMetaInfo)) {
                throw new \RuntimeException(sprintf('Missing "%s" in input JSON', $key));
            }
        }

        return self::create(
            $pageMetaInfo[self::KEY_ROOT_SNIPPET_CODE],
            $pageMetaInfo[self::KEY_PAGE_SNIPPET_CODES],
            $pageMetaInfo[self::KEY_CONTAINER_SNIPPETS]
        );
    }

    /**
     * @return mixed[]
     */
    public function getInfo()
    {
        return [
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
        ];
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

    /**
     * @return array[]
     */
    public function getContainerSnippets()
    {
        return array_reduce($this->snippetContainers, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
