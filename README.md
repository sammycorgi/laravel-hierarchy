# laravel-hierarchy
Single root node hierarchy implementation for Laravel

# Installation

You can install the package via composer:

`composer require sammycorgi/laravel-hierarchy`

Publish the config by running the following console command: 

`php artisan vendor:publish --provider="Sammycorgi\LaravelHierarchy\HierarchyServiceProvider" --tag="config"`

If using the `EloquentPersister`, publish the migration for HierarchyModelChildRecord by running the following console command:

`php artisan vendor:publish --provider="Sammycorgi\LaravelHierarchy\HierarchyServiceProvider" --tag="migrations"`

Also make sure to set `hierarchy.eloquent.default_model` in the config to your default `HasHierarchy` Model if you're going to use the `EloquentPersister`

# Usage

Implement the `Contracts\HasHierarchy` interface on any `Model`. For ease, use the `Traits\HasHierarchy` trait to implement the methods automatically.
By default, the ID/Parent ID properties are `id` and `parent_id`, respectively.
Using this trait, whenever a record is created, deleted or one has its `parent_id` updated, all affected parents will have their persisted child IDs updated automatically (see below).

Inject (or otherwise instantiate) `HierarchyServiceFactory` and pass a `Model` which implements `HasHierarchy` into the `getChildIdsFor()` method to return a collection of IDs which belong to children of the passed model.
Call the `for()` method to return a `HierarchyService` with dependencies automatically resolved. You may also pass arguments to this method to override the default dependencies.

## Persisting Child IDs

This package makes use of persistence via the cache (by default) or eloquent for quicker fetching of a model's children. 
If you wish to implement your own persister, create a new class that implements the `Contracts\PersistsHierarchyChildren` interface.

The default persister is the `CachePersister`, but this can be modified in the config.
Simply change `hierarchy.persisters.default` in the config to the value of another key in this array, or change the `HIERARCHY_DEFAULT_PERSISTER` in your `.env` file.

Whenever the hierarchy tree is built for a given model, the child IDs will be persisted forever using the `CachePersister`.

When using the `EloquentPersister`, the database will be used to persist these child IDs. 
New records will be added to the `hierarchy_model_child_records` table that include the model type, the child ID and the parent ID.

## Fetching Models

By default, this package uses the `EloquentHierarchyInstanceGetter` to fetch `HasHierarchy` models. 
If you wish to implement your own instance getter, create a new class that implements the `Contracts\GetsHierarchyInstances` interface.

The default instance getter is the `EloquentHierarchyInstanceGetter`, but this can be modified in the config.
Simply change `hierarchy.instance_getters.default` in the config to the value of another key in this array.
