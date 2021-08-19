<?php

namespace Sammycorgi\LaravelHierarchy\InstanceGetter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

class EloquentHierarchyInstanceGetter implements GetsHierarchyInstances
{
    /**
     * @var HasHierarchy|Model
     */
    private $class;
    private string $classname;

    /**
     * EloquentHierarchyInstanceGetter constructor.
     */
    public function __construct(string $classname)
    {
        $this->classname = $classname;
        $this->class = app()->make($classname);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->class->count();
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return $this->class->select([$this->class->getIdKeyName(), $this->class->getParentIdKeyName()])->get();
    }

    /**
     * @inheritDoc
     */
    public function hasChildren(int|string $id): bool
    {
        return $this->class->where($this->class->getParentIdKeyName(), $id)->count() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getTypeIdentifier(): string
    {
        return $this->class->getMorphClass();
    }

    /**
     * @return Model|HasHierarchy
     */
    public function getClass(): mixed
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }
}