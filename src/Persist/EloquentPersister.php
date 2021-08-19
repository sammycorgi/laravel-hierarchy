<?php


namespace Sammycorgi\LaravelHierarchy\Persist;


use Closure;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;
use Sammycorgi\LaravelHierarchy\Models\HierarchyModelChildRecord;

class EloquentPersister implements PersistsHierarchyChildren
{
    /**
     * @inheritDoc
     */
    public function get(string $type, int|string $parentId): ?Collection
    {
        return HierarchyModelChildRecord::whereType($type)->whereParentId($parentId)->pluck('child_id');
    }

    /**
     * @inheritDoc
     */
    public function has(string $type, int|string $parentId): bool
    {
        return HierarchyModelChildRecord::whereType($type)->whereParentId($parentId)->exists();
    }

    /**
     * @inheritDoc
     */
    public function insertOne(string $type, int|string $parentId, int|string $childId): Collection
    {
        HierarchyModelChildRecord::create(['type' => $type, 'child_id' => $childId, 'parent_id' => $parentId]);

        return $this->get($type, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function deleteOne(string $type, int|string $parentId, int|string $childId): Collection
    {
        HierarchyModelChildRecord::where(['type' => $type, 'child_id' => $childId, 'parent_id' => $parentId])->delete();

        return $this->get($type, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function put(string $type, int|string $parentId, Collection $childIds): bool
    {
        HierarchyModelChildRecord::insert($childIds->map(function(string|int $childId) use ($type, $parentId) {
            return ['type' => $type, 'child_id' => $childId, 'parent_id' => $parentId];
        })->toArray());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function putIfNew(string $type, int|string $parentId, Closure $closure): Collection
    {
        if(!$this->has($type, $parentId)) {
            $this->put($type, $parentId, $closure());
        }

        return $this->get($type, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $type, int|string $parentId): bool
    {
        return HierarchyModelChildRecord::whereType($type)->whereParentId($parentId)->delete();
    }
}