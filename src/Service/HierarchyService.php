<?php


namespace Sammycorgi\LaravelHierarchy\Service;

use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;
use Sammycorgi\LaravelHierarchy\Exceptions\HierarchyException;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeBuilder;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeNode;

class HierarchyService
{
    private int $hierarchyCount;

    /**
     * @var HierarchyTreeBuilder
     */
    private HierarchyTreeBuilder $hierarchyTreeBuilder;

    /**
     * @var PersistsHierarchyChildren
     */
    private PersistsHierarchyChildren $persister;

    /**
     * @var GetsHierarchyInstances
     */
    private GetsHierarchyInstances $hierarchyInstanceGetter;

    /**
     * @var HierarchyTreeNode|null
     */
    private ?HierarchyTreeNode $completeTree = null;

    /**
     * modelHierarchyService constructor.
     * @param HierarchyTreeBuilder $hierarchyTreeBuilder
     * @param PersistsHierarchyChildren $persister
     * @param GetsHierarchyInstances $hierarchyInstanceGetter
     */
    public function __construct(HierarchyTreeBuilder $hierarchyTreeBuilder, PersistsHierarchyChildren $persister, GetsHierarchyInstances $hierarchyInstanceGetter)
    {
        $this->hierarchyTreeBuilder = $hierarchyTreeBuilder;
        $this->persister = $persister;
        $this->hierarchyInstanceGetter = $hierarchyInstanceGetter;
    }

    /**
     * Build a tree from all models and return the parent node
     *
     * @return HierarchyTreeNode
     * @throws HierarchyException
     */
    public function getTreeForAll(): HierarchyTreeNode
    {
        $count = $this->hierarchyInstanceGetter->count();

        if (isset($this->hierarchyCount)) {
            if ($this->hierarchyCount !== $count) {
                $this->completeTree = null;
            }
        }

        $this->hierarchyCount = $count;

        if ($this->completeTree === null) {
            $this->completeTree = $this->hierarchyTreeBuilder->build($this->hierarchyInstanceGetter->getAll());
        }

        return $this->completeTree;
    }

    /**
     * Return a collection of all model IDs that belong to children of a given model (including the passed model)
     *
     * @param string|int $id
     * @return Collection
     */
    public function getChildIdsForId(string|int $id): Collection
    {
        return $this->persister->putIfNew($this->hierarchyInstanceGetter->getTypeIdentifier(), $id, function () use ($id) {
            //don't bother building the tree if the passed model doesn't have any children
            if (!$this->hierarchyInstanceGetter->hasChildren($id)) {
                return new Collection($id);
            }

            $tree = $this->getTreeForAll();

            //if given model is root node
            if ($tree->getItem()->getId() == $id) {
                $found = $tree;
            } else {
                //otherwise look for the given child on the root of children
                $found = collect($tree->getAllChildren())->filter(function (HierarchyTreeNode $child) use ($id) {
                    return $child->getItem()->getId() == $id;
                })->first();
            }

            return Collection::wrap(optional($found)->getAllChildren())->map(function (HierarchyTreeNode $child) {
                return $child->getItem();
            })->prepend($found->getItem())->map(function (HasHierarchy $hasHierarchy) {
                return $hasHierarchy->getId();
            });
        });
    }

    /**
     * @param string|int $id
     * @param bool $regenerateNow
     */
    public function deleteChildRecords(string|int $id, bool $regenerateNow = false): void
    {
        $this->persister->delete($this->hierarchyInstanceGetter->getTypeIdentifier(), $id);

        if ($regenerateNow) {
            $this->getChildIdsForId($id);
        }
    }

    /**
     * Add an ID into a hierarchy tree, then return the updated IDs
     *
     * @param string|int $id
     * @param string|int $insertedId
     * @return Collection
     */
    public function insertChildRecordForId(string|int $id, string|int $insertedId): Collection
    {
        return $this->persister->insertOne($this->hierarchyInstanceGetter->getTypeIdentifier(), $id, $insertedId);
    }

    /**
     * Determine if the child IDs have been persisted for a given ID
     *
     * @param string|int $id
     * @return bool
     */
    public function has(string|int $id) : bool
    {
        return $this->persister->has($this->hierarchyInstanceGetter->getTypeIdentifier(), $id);
    }

    /**
     * Delete an ID from a hierarchy tree, then return the updated IDs
     *
     * @param string|int $id
     * @param string|int $deletedId
     * @return Collection
     */
    public function deleteChildIdForId(string|int $id, string|int $deletedId): Collection
    {
        return $this->persister->deleteOne($this->hierarchyInstanceGetter->getTypeIdentifier(), $id, $deletedId);
    }

    /**
     * @return GetsHierarchyInstances
     */
    public function getHierarchyInstanceGetter(): GetsHierarchyInstances
    {
        return $this->hierarchyInstanceGetter;
    }
}
