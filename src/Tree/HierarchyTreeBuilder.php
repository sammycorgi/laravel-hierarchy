<?php


namespace Sammycorgi\LaravelHierarchy\Tree;


use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;
use Sammycorgi\LaravelHierarchy\Exceptions\HierarchyException;

class HierarchyTreeBuilder
{
    /**
     * Build a hierarchy tree from a collection of HasHierarchy objects
     *
     * @param Collection $items
     * @return HierarchyTreeNode
     * @throws HierarchyException
     */
    public function build(Collection $items): HierarchyTreeNode
    {
        $collection = collect($items)->transform(function (HasHierarchy $item) {
            return new HierarchyTreeNode($item);
        });

        $groupedByParentId = $collection->groupBy(function (HierarchyTreeNode $item) {
            return $item->getItem()->getParentId();
        });

        $groupedByParentId->each(function (Collection $group, $parentId) use ($collection) {
            if (!blank($parentId)) {
                $parent = $collection->first(function (HierarchyTreeNode $item) use ($parentId) {
                    return $item->getItem()->getId() === $parentId;
                });

                if ($parent) {
                    $parent->setImmediateChildren($group->values());
                }
            }
        });

        return $this->getRootNode($collection);
    }

    /**
     * Find the root node from an array of items
     *
     * @param Collection $items
     * @return HierarchyTreeNode
     * @throws HierarchyException
     */
    protected function getRootNode(Collection $items): HierarchyTreeNode
    {
        $roots = $items->filter(function (HierarchyTreeNode $item) {
            return $item->getItem()->getParentId() === null;
        });

        if ($roots->count() !== 1) {
            HierarchyException::throwInvalidRootCountException($roots->count());
        }

        return $roots->first();
    }
}
