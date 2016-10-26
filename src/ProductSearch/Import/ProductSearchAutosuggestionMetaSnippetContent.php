<?php

declare(strict_types=1);

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
    private function __construct(string $rootSnippetCode, array $pageSnippetCodes, array $snippetContainers)
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
    public static function create(
        string $rootSnippetCode,
        array $pageSnippetCodes,
        array $snippetContainerData
    ) : ProductSearchAutosuggestionMetaSnippetContent {
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
    private static function createSnippetContainers(array $containerArray) : array
    {
        return array_map(function ($code) use ($containerArray) {
            return new SnippetContainer($code, $containerArray[$code]);
        }, array_keys($containerArray));
    }

    public static function fromJson(string $json) : ProductSearchAutosuggestionMetaSnippetContent
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
    public function getInfo() : array
    {
        return [
            self::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            self::KEY_PAGE_SNIPPET_CODES => $this->pageSnippetCodes,
            self::KEY_CONTAINER_SNIPPETS => $this->getContainerSnippets(),
        ];
    }

    public function getRootSnippetCode() : string
    {
        return $this->rootSnippetCode;
    }

    /**
     * @return string[]
     */
    public function getPageSnippetCodes() : array
    {
        return $this->pageSnippetCodes;
    }

    /**
     * @return array[]
     */
    public function getContainerSnippets() : array
    {
        return array_reduce($this->snippetContainers, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
