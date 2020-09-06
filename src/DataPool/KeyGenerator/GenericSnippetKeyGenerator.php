<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\MissingSnippetKeyGenerationDataException;
use LizardsAndPumpkins\Util\SnippetCodeValidator;

class GenericSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var string
     */
    private $snippetCode;
    
    /**
     * @var string[]
     */
    private $contextParts;

    /**
     * @var string[]
     */
    private $usedDataParts;

    /**
     * @param string $snippetCode
     * @param string[] $contextParts
     * @param string[] $usedDataParts
     */
    public function __construct(string $snippetCode, array $contextParts, array $usedDataParts)
    {
        SnippetCodeValidator::validate($snippetCode);

        $this->snippetCode = $snippetCode;
        $this->contextParts = $contextParts;
        $this->usedDataParts = $usedDataParts;
    }

    /**
     * @param Context $context
     * @param mixed[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data) : string
    {
        $this->validateDataContainsRequiredParts($data);

        $snippetKeyData = $this->getSnippetKeyDataAsString($data);
        $snippetKey = $this->snippetCode . $snippetKeyData . '_' . $context->getIdForParts(...$this->contextParts);

        return $snippetKey;
    }

    /**
     * @param mixed[] $data
     */
    private function validateDataContainsRequiredParts(array $data): void
    {
        $missingDataParts = array_diff($this->usedDataParts, array_keys($data));

        if (count($missingDataParts) > 0) {
            throw new MissingSnippetKeyGenerationDataException(
                sprintf('"%s" is missing in snippet generation data.', implode(', ', $missingDataParts))
            );
        }
    }

    /**
     * @param string[] $data
     * @return string
     */
    private function getSnippetKeyDataAsString(array $data) : string
    {
        return array_reduce($this->usedDataParts, function ($carry, $dataKey) use ($data) {
            return $carry . '_' . $data[$dataKey];
        }, '');
    }
}
