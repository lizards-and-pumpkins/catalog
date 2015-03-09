<?php

namespace Brera;

use Brera\Context\Context;

class RootSnippetSource
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var int
     */
    private $numItemsPerPage;

    /**
     * @param Context $context
     * @param int $numItemsPerPage
     */
    public function __construct(Context $context, $numItemsPerPage)
    {
        $this->context = $context;
        $this->numItemsPerPage = (int) $numItemsPerPage;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getNumItemsPerPage()
    {
        return $this->numItemsPerPage;
    }
}
