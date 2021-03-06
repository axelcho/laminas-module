<?php

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Selectable as DoctrineSelectable;
use Doctrine\Common\Collections\Criteria;
use Laminas\Paginator\Adapter\AdapterInterface;

/**
 * Provides a wrapper around a Selectable object
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Selectable implements AdapterInterface
{
    /**
     * @var DoctrineSelectable
     */
    protected $selectable;

    /**
     * @var Criteria
     */
    protected $criteria;

    /**
     * Create a paginator around a Selectable object. You can also provide an optional Criteria object with
     * some predefined filters
     *
     * @param DoctrineSelectable    $selectable
     * @param Criteria|null $criteria
     */
    public function __construct(DoctrineSelectable $selectable, Criteria $criteria = null)
    {
        $this->selectable = $selectable;
        $this->criteria   = $criteria ? clone $criteria : new Criteria();
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->criteria->setFirstResult($offset)->setMaxResults($itemCountPerPage);

        return $this->selectable->matching($this->criteria)->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $criteria = clone $this->criteria;

        $criteria->setFirstResult(null);
        $criteria->setMaxResults(null);

        return count($this->selectable->matching($criteria));
    }
}
