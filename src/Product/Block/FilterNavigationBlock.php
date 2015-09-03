<?php

namespace Brera\Product\Block;

use Brera\Product\FilterNavigationFilterCollection;
use Brera\Renderer\Block;
use Brera\Renderer\InvalidDataObjectException;

class FilterNavigationBlock extends Block
{
    /**
     * @return FilterNavigationFilterCollection
     */
    public function getFilterCollection()
    {
        $this->validateDataObject();
        return $this->getDataObject();
    }

    private function validateDataObject()
    {
        $dataObject = $this->getDataObject();
        if (!($dataObject instanceof FilterNavigationFilterCollection)) {
            throw new InvalidDataObjectException(
                sprintf(
                    'Data object must be instance of %s, got "%s".',
                    FilterNavigationFilterCollection::class,
                    $this->getVariableType($dataObject)
                )
            );
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return 'object' !== gettype($variable) ? gettype($variable) : get_class($variable);
    }
}
