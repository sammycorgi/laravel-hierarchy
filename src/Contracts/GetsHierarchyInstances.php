<?php


namespace Sammycorgi\LaravelHierarchy\Contracts;


use Illuminate\Support\Collection;

interface GetsHierarchyInstances
{
    /**
     * Get the number of all HasHierarchy
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get all of the HasHierarchy instances
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Determine if an ID has any children
     *
     * @param string|int $id
     * @return bool
     */
    public function hasChildren(string|int $id): bool;

    /**
     * Get the identifier to be used for caching
     *
     * @return string
     */
    public function getTypeIdentifier(): string;
}