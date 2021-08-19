<?php

use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Sammycorgi\LaravelHierarchy\Persist\CachePersister;
use Sammycorgi\LaravelHierarchy\Persist\EloquentPersister;

return [
    'instance_getters' => [
        'default' => 'eloquent',
        'eloquent' => EloquentHierarchyInstanceGetter::class
    ],

    'eloquent' => [
        'default_model' => 'DEFINE_THIS'
    ],

    'persisters' => [
        'default' => env('HIERARCHY_DEFAULT_PERSISTER', 'cache'),
        'cache' => CachePersister::class,
        'eloquent' => EloquentPersister::class
    ]
];