<?php

namespace Sammycorgi\LaravelHierarchy\InstanceGetter;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

class EloquentHierarchyInstanceGetter implements GetsHierarchyInstances
{
    private Model $class;
    private string $classname;
    private string $parentIdProperty;
    private string $idProperty;

    /**
     * EloquentHierarchyInstanceGetter constructor.
     */
    public function __construct(string $classname)
    {
        $this->classname = $classname;

        $this->class = new $classname;

        if(!is_subclass_of($this->class, HasHierarchy::class)) {
            throw new Exception("$classname must implement interface " . HasHierarchy::class);
        }

        $this->parentIdProperty = $this->class->getParentIdKeyName();
        $this->idProperty = $this->class->getIdKeyName();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return call_user_func([$this->classname, 'count']);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return call_user_func([$this->classname, 'select'], [$this->idProperty, $this->parentIdProperty])->get();
    }

    /**
     * @inheritDoc
     */
    public function hasChildren(int|string $id): bool
    {
        return call_user_func([$this->classname, 'where'], $this->parentIdProperty, $id)->count() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getTypeIdentifier(): string
    {
        return $this->class->getMorphClass();
    }
}