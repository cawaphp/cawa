<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Orm;

class CollectionPaged extends Collection
{
    /**
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        parent::__construct($elements);
        $this->pageSize = sizeof($elements);
    }

    /**
     * @var int
     */
    private $count;

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return $this->count;
    }

    /**
     * @var int
     */
    private $pageSize = 20;

    /**
     * @return int
     */
    public function getPageSize() : int
    {
        return $this->pageSize;
    }

    /**
     * @var int
     */
    private $currentPage = 1;

    /**
     * @return int
     */
    public function getCurrentPage() : int
    {
        return $this->currentPage;
    }

    /**
     * Get Page Count.
     *
     * @return int
     */
    public function getPageCount() : int
    {
        return intval($this->count / $this->pageSize) + ($this->count % $this->pageSize == 0 ? 0 : 1);
    }

    /**
     * Returns if the number of results is higher than the max per page.
     *
     * @return bool
     */
    public function hasPagination() : bool
    {
        return $this->count > $this->pageSize;
    }

    /**
     * Returns whether there is previous page or not.
     *
     * @return bool
     */
    public function hasPreviousPage() : bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Returns whether there is next page or not.
     *
     * @return bool
     */
    public function hasNextPage() : bool
    {
        return $this->currentPage < $this->getPageCount();
    }
}
