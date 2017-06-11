<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

class SnippetContainer
{
    /**
     * @var SnippetCode
     */
    private $containerCode;

    /**
     * @var SnippetCode[]
     */
    private $containedSnippetCodes;

    public function __construct(SnippetCode $containerCode, SnippetCode ...$containedSnippetCodes)
    {
        $this->containerCode = $containerCode;
        $this->containedSnippetCodes = $containedSnippetCodes;
    }

    public static function rehydrate(string $code, string ...$containedSnippetCodeStrings): SnippetContainer
    {
        $containedSnippetCodes = array_map(function (string $containerSnippetCodeString) {
            return new SnippetCode($containerSnippetCodeString);
        }, $containedSnippetCodeStrings);

        return new static(new SnippetCode($code), ...$containedSnippetCodes);
    }

    /**
     * @return SnippetCode[]
     */
    public function toArray(): array
    {
        return [(string) $this->containerCode => $this->containedSnippetCodes];
    }
}
