<?php


namespace Sammycorgi\LaravelHierarchy\Contracts;

use Closure;
use Illuminate\Support\Collection;

interface PersistsHierarchyChildren
{
    /**
     * Get the IDs for the given ID, if they exist
     *
     * @param string $type
     * @param string|int $parentId
     * @return Collection|null
     */
    public function get(string $type, string|int $parentId): ?Collection;

    /**
     * Determine if the records for a given ID and type exist
     *
     * @param string $type
     * @param string|int $parentId
     * @return bool
     */
    public function has(string $type, string|int $parentId) : bool;

    /**
     * Add a child ID into the given model's stored collection and return the updated collection
     *
     * @param string $type
     * @param string|int $parentId
     * @param string|int $childId
     * @return Collection
     */
    public function insertOne(string $type, string|int $parentId, string|int $childId): Collection;

    /**
     * Delete a child ID from the given model's stored collection and return the updated collection
     *
     * @param string $type
     * @param string|int $parentId
     * @param string|int $childId
     * @return Collection
     */
    public function deleteOne(string $type, string|int $parentId, string|int $childId): Collection;

    /**
     * Persist a collection of IDs
     *
     * @param string $type
     * @param string|int $parentId
     * @param Collection $childIds
     * @return mixed
     */
    public function put(string $type, string|int $parentId, Collection $childIds): bool;

    /**
     * Return the persisted records, or store them using the given closure
     *
     * @param string $type
     * @param string|int $parentId
     * @param Closure $closure
     * @return Collection
     */
    public function putIfNew(string $type, string|int $parentId, Closure $closure): Collection;

    /**
     * Delete the persisted hierarchy children
     *
     * @param string $type
     * @param string|int $parentId
     * @return bool
     */
    public function delete(string $type, string|int $parentId): bool;
}