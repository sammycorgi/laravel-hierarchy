<?php

namespace Sammycorgi\LaravelHierarchy\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sammycorgi\LaravelHierarchy\Exceptions\HierarchyException;
use Sammycorgi\LaravelHierarchy\Service\HierarchyService;
use Sammycorgi\LaravelHierarchy\Service\HierarchyServiceFactory;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy as HierarchyContract;

trait HasHierarchy
{
    /**
     * BootHasHierarchy constructor.
     */
    public static function bootHasHierarchy()
    {
        static::creating(function (HierarchyContract|Model $model) {
            //ensure that the parent id is not null
            $model->checkHierarchyRootNodes();
        });

        static::created(function (HierarchyContract|Model $model) {
            //add the newly created ID to the child ID array for all parents
            $model->recursivelyUpdateHierarchy($model->parent, false);
        });

        static::deleted(function (HierarchyContract|Model $model) {
            //remote the deleted ID from the child ID array for all parents
            $model->recursivelyUpdateHierarchy($model->parent, true);
        });

        static::updating(function (HierarchyContract|Model $model) {
            //if parent id is being set to null, ensure that there is no root node
            $model->checkHierarchyRootNodes();
        });

        static::updated(function (HierarchyContract|Model $model) {
            //if parent id is changed
            if(isset($model->getDirty()[$model->getParentIdKeyName()])) {
                $newParent = $model->find($model->getParentId());
                $oldParent = $model->find($model->getOriginal($model->getParentIdKeyName()));

                //delete the model id from all previous parents
                $model->recursivelyUpdateHierarchy($oldParent, true);

                //and add to new parents
                $model->recursivelyUpdateHierarchy($newParent, false);
            }
        });
    }

    /**
     * @return HierarchyService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function makeHierarchyService() : HierarchyService
    {
        return app()->make(HierarchyServiceFactory::class)->for(static::class);
    }

    /**
     * @throws HierarchyException
     */
    private function checkHierarchyRootNodes() : void
    {
        if($this->getParentId() === null) {
            if($this->whereNull($this->getParentIdKeyName())->count() !== 0) {
                HierarchyException::throwInvalidRootCountException();
            }
        }
    }

    /**
     * @param static $parent
     * @param bool $delete
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function recursivelyUpdateHierarchy($parent, bool $delete = false)
    {
        $hierarchyService = $this->makeHierarchyService();

        while($parent !== null) {
            if(!$hierarchyService->has($parent->getId())) {
                $hierarchyService->getChildIdsForId($parent->getId());
            }

            if($delete) {
                $hierarchyService->deleteChildIdForId($parent->getId(), $this->getId());
            } else {
                $hierarchyService->insertChildRecordForId($parent->getId(), $this->getId());
            }

            $parent = $parent->parent;
        }
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): string|int|null {
        return $this->{$this->getParentIdKeyName()};
    }

    /**
     * @inheritDoc
     */
    public function getId(): string|int {
        return $this->{$this->getIdKeyName()};
    }

    /**
     * @inheritDoc
     */
    public function getParentIdKeyName() : string {
        if(isset($this->parentIdKeyName)) {
            return $this->parentIdKeyName;
        }

        return "parent_id";
    }

    /**
     * @inheritDoc
     */
    public function getIdKeyName() : string {
        if(isset($this->idKeyName)) {
            return $this->idKeyName;
        }

        return $this->getKeyName();
    }

    /**
     * @inheritDoc
     */
    public function parent() : BelongsTo {
        return $this->belongsTo(static::class, $this->getParentIdKeyName(), $this->getIdKeyName());
    }

    /**
     * @inheritDoc
     */
    public function immediateChildren() : HasMany {
        return $this->hasMany(static::class, $this->getParentIdKeyName(), $this->getIdKeyName());
    }
}