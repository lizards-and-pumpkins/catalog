<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetContainer;

class ProductSearchResultMetaSnippetContent implements PageMetaInfoSnippetContent
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
     * @var array|\LizardsAndPumpkins\SnippetContainer[]
     */
    private $containerSnippets;

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param SnippetContainer[] $containerSnippets
     */
    private function __construct($rootSnippetCode, array $pageSnippetCodes, array $containerSnippets)
    {
        $this->rootSnippetCode = $rootSnippetCode;
        $this->pageSnippetCodes = $pageSnippetCodes;
        $this->containerSnippets = $containerSnippets;
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @param array[] $containerData
     * @return ProductSearchResultMetaSnippetContent
     */
    public static function create($rootSnippetCode, array $pageSnippetCodes, array $containerData)
    {
        if (!is_string($rootSnippetCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The page meta info root snippet code has to be a string value, got "%s"',
                gettype($rootSnippetCode)
            ));
        }

        if (!in_array($rootSnippetCode, $pageSnippetCodes)) {
            $pageSnippetCodes = array_merge([$rootSnippetCode], $pageSnippetCodes);
        }
        return new self($rootSnippetCode, $pageSnippetCodes, self::createSnippetContainers($containerData));
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
     * @return ProductSearchResultMetaSnippetContent
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
        return array_reduce($this->containerSnippets, function ($carry, SnippetContainer $container) {
            return array_merge($carry, $container->toArray());
        }, []);
    }
}
