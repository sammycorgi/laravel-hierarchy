<?php


namespace Sammycorgi\LaravelHierarchy\Persist;

use Closure;
use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;

class CachePersister implements PersistsHierarchyChildren
{
    private Repository $cache;

    /**
     * CachePersister constructor.
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $type
     * @param string|int $id
     * @return string
     */
    private function getTreeCacheKey(string $type, string|int $id): string
    {
        return "$type-hierarchy-children-for-$id";
    }

    /**
     * @inheritDoc
     */
    public function get(string $type, int|string $parentId): ?Collection
    {
        return $this->cache->get($this->getTreeCacheKey($type, $parentId));
    }

    /**
     * @inheritDoc
     */
    public function putIfNew(string $type, int|string $parentId, Closure $closure): Collection
    {
        return $this->cache->rememberForever($this->getTreeCacheKey($type, $parentId), $closure);
    }

    /**
     * @inheritDoc
     */
    public function put(string $type, int|string $parentId, Collection $childIds): bool
    {
        return $this->cache->put($this->getTreeCacheKey($type, $parentId), $childIds);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $type, int|string $parentId): bool
    {
        return $this->cache->delete($this->getTreeCacheKey($type, $parentId));
    }

    /**
     * @inheritDoc
     */
    public function insertOne(string $type, int|string $parentId, int|string $childId): Collection
    {
        $childIds = $this->get($type, $parentId) ?? new Collection([$parentId]);

        if ($childIds === null) {
            throw new Exception("Cannot insert a new child ID into hierarchy cache because the tree hasn't been built for this model yet!");
        }

        $childIds->push($childId);

        $this->put($type, $parentId, $childIds);

        return $childIds;
    }

    /**
     * @inheritDoc
     */
    public function deleteOne(string $type, int|string $parentId, int|string $childId): Collection
    {
        $childIds = $this->get($type, $parentId);

        if ($childIds === null) {
            throw new Exception("Cannot delete a child ID from hierarchy cache because the tree hasn't been built for this model yet!");
        }

        $childIds->pull($childIds->search(function ($item) use ($childId) {
            return $item == $childId;
        }));

        $this->put($type, $parentId, $childIds);

        return $childIds;
    }

    /**
     * @inheritDoc
     */
    public function has(string $type, int|string $parentId): bool
    {
        return $this->cache->has($this->getTreeCacheKey($type, $parentId));
    }
}