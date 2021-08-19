<?php


namespace Sammycorgi\LaravelHierarchy\Tree;


use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

/**
 * A node of a hierarchy tree
 * Has one or no parent and any number of children
 *
 * Class HierarchyTreeNode
 * @package Sammycorgi\LaravelHierarchy\Hierarchy\Tree\HierarchyTree
 */
class HierarchyTreeNode
{
    /**
     * @var HasHierarchy
     */
    protected HasHierarchy $item;

    /**
     * The children immediately below this node (if not at the bottom of the tree)
     *
     * @var Collection
     */
    protected Collection $immediateChildren;

    /**
     * The parent of this node (if not at the top of the tree)
     *
     * @var HierarchyTreeNode|null
     */
    protected ?HierarchyTreeNode $parent = null;

    /**
     * A collection of all children for this node, regardless of distance
     * Used for caching
     *
     * @var Collection|null
     */
    protected ?Collection $allChildren = null;

    public function __construct(HasHierarchy $item)
    {
        $this->item = $item;
        $this->immediateChildren = new Collection;
    }

    /**
     * @return Collection
     */
    public function getImmediateChildren(): Collection
    {
        return $this->immediateChildren;
    }

    /**
     * Get all the children for this node from memory, or process it if it hasn't been done yet
     *
     * @return Collection
     */
    public function getAllChildren(): Collection
    {
        if ($this->allChildren === null) {
            $this->allChildren = $this->processAllChildren($this->immediateChildren);
        }

        return $this->allChildren;
    }

    /**
     * Get all of the descendents of this node, regardless of distance
     *
     * @param Collection|null $nextStep
     * @param Collection|null $found
     * @return Collection
     */
    protected function processAllChildren(?Collection $nextStep = null, ?Collection $found = null): Collection
    {
        //if there is more processing to be done
        if ($nextStep !== null && $nextStep->isNotEmpty()) {
            $next = new Collection;

            //get the children for each child
            foreach ($nextStep as $child) {
                $next = $next->merge($child->getImmediateChildren());
            }

            //and merge the current pass into the found children
            $found = $nextStep->merge($found);

            //and repeat the process
            return $this->processAllChildren($next, $found);
        }

        //otherwise return the results
        return Collection::wrap($found);
    }

    /**
     * @return HierarchyTreeNode|null
     */
    public function getParent(): ?HierarchyTreeNode
    {
        return $this->parent;
    }

    /**
     * @return HasHierarchy
     */
    public function getItem(): HasHierarchy
    {
        return $this->item;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->immediateChildren->isNotEmpty();
    }

    /**
     * @param Collection $immediateChildren
     */
    public function setImmediateChildren(Collection $immediateChildren): void
    {
        $this->immediateChildren = $immediateChildren;

        foreach ($immediateChildren as $child) {
            $child->setParent($this);
        }
    }

    /**
     * Determine if this node is the immediate parent of another node
     *
     * @param HierarchyTreeNode $node
     * @return bool
     */
    public function isImmediateParentOf(HierarchyTreeNode $node): bool
    {
        return $this === $node->getParent();
    }

    /**
     * Determine if this node is the immediate child of another node
     *
     * @param HierarchyTreeNode $node
     * @return bool
     */
    public function isImmediateChildOf(HierarchyTreeNode $node): bool
    {
        return $this->parent === $node;
    }

    /**
     * Determine if this node is a parent of another node from any depth
     *
     * @param HierarchyTreeNode $node
     * @return bool
     */
    public function isAnyDepthParentOf(HierarchyTreeNode $node): bool
    {
        //if this has no parent all other nodes are descendents of it
        return $this->item->getParentId() === null
            || $this->getAllChildren()->contains($node);
    }

    /**
     * Determine if this node is a child of another node from any depth
     *
     * @param HierarchyTreeNode $node
     * @return bool
     */
    public function isAnyDepthChildOf(HierarchyTreeNode $node): bool
    {
        //if other node has no parent it is a parent of this (single root tree)
        return $node->getItem()->getParentId() === null
            || $node->getAllChildren()->contains($this);
    }

    /**
     * @param HierarchyTreeNode $parent
     */
    public function setParent(HierarchyTreeNode $parent): void
    {
        $this->parent = $parent;
    }
}
