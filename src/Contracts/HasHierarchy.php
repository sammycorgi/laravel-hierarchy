<?php


namespace Sammycorgi\LaravelHierarchy\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Used with the HierarchyTreeNode to build a hierarchy tree
 *
 * Interface HasHierarchy
 * @see HierarchyTreeNode
 *
 * @property-read HasHierarchy|Model|null $parent
 * @property-read HasHierarchy[]|Model[]|Collection $immediateChildren
 */
interface HasHierarchy
{
    /**
     * Return the parent ID of this object
     *
     * @return string | int | null
     */
    public function getParentId(): string|int|null;

    /**
     * Return the ID of this object
     *
     * @return string | int
     */
    public function getId(): string|int;

    /**
     * @return string
     */
    public function getParentIdKeyName() : string;

    /**
     * @return string
     */
    public function getIdKeyName() : string;

    /**
     * @return BelongsTo
     */
    public function parent() : BelongsTo;

    /**
     * @return HasMany
     */
    public function immediateChildren() : HasMany;
}
