<?php


namespace Sammycorgi\LaravelHierarchy\Service;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;
use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;

class HierarchyServiceFactory
{
    /**
     * Generate a hierarchy service for the given hierarchy type
     *
     * @param string|GetsHierarchyInstances|Model|null $instanceGetterOrModelOrClass
     * @param string|PersistsHierarchyChildren|null $persisterOrClass
     * @return HierarchyService
     * @throws BindingResolutionException
     */
    public function for(string|GetsHierarchyInstances|Model|null $instanceGetterOrModelOrClass = null, string|PersistsHierarchyChildren|null $persisterOrClass = null): HierarchyService
    {
        return app()->make(HierarchyService::class,
            [
                "persister" => $this->makePersister($persisterOrClass),
                "hierarchyInstanceGetter" => $this->makeInstanceGetter($instanceGetterOrModelOrClass)
            ]);
    }

    /**
     * Get the child IDs for the given model using the eloquent instance getter
     *
     * @param Model|HasHierarchy $model
     * @return Collection
     * @throws BindingResolutionException
     */
    public function getChildIdsFor(Model|HasHierarchy $model): Collection
    {
        return $this->for($model)->getChildIdsForId($model->getId());
    }

    /**
     * @return HierarchyService
     * @throws BindingResolutionException
     */
    public function service(): HierarchyService
    {
        return $this->for(null, null);
    }

    /**
     * @param string|GetsHierarchyInstances|Model|null $instanceGetterOrModelOrClass
     * @return GetsHierarchyInstances
     * @throws BindingResolutionException
     */
    private function makeInstanceGetter(string|GetsHierarchyInstances|Model|null $instanceGetterOrModelOrClass): GetsHierarchyInstances
    {
        if ($instanceGetterOrModelOrClass === null) {
            return new EloquentHierarchyInstanceGetter(config('hierarchy.eloquent.default_model'));
        }

        if ($instanceGetterOrModelOrClass instanceof GetsHierarchyInstances) {
            return $instanceGetterOrModelOrClass;
        }

        if (is_subclass_of($instanceGetterOrModelOrClass, GetsHierarchyInstances::class)) {
            return app()->make($instanceGetterOrModelOrClass);
        }

        if (is_subclass_of($instanceGetterOrModelOrClass, Model::class)) {
            return new EloquentHierarchyInstanceGetter(is_object($instanceGetterOrModelOrClass) ? get_class($instanceGetterOrModelOrClass) : $instanceGetterOrModelOrClass);
        }

        return app()->make(config('hierarchy.instance_getters.' . $instanceGetterOrModelOrClass));
    }

    /**
     * @param string|GetsHierarchyInstances|null $persisterOrClass
     * @return PersistsHierarchyChildren
     * @throws BindingResolutionException
     */
    private function makePersister(string|GetsHierarchyInstances|null $persisterOrClass): PersistsHierarchyChildren
    {
        if ($persisterOrClass === null) {
            return app()->make(config('hierarchy.persisters.' . config('hierarchy.persisters.default')));
        }

        if ($persisterOrClass instanceof PersistsHierarchyChildren) {
            return $persisterOrClass;
        }

        if (is_subclass_of($persisterOrClass, PersistsHierarchyChildren::class)) {
            return app()->make($persisterOrClass);
        }

        return app()->make(config('hierarchy.persisters.' . $persisterOrClass));
    }
}