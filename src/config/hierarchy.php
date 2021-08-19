<?php

use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Sammycorgi\LaravelHierarchy\Persist\CachePersister;

return [
    'instance_getters' => [
        'eloquent' => EloquentHierarchyInstanceGetter::class,
    ],

    'eloquent' => [
        'default_model' => 'DEFINE_THIS'
    ],

    'persisters' => [
        'default' => 'cache',
        'cache' => CachePersister::class
    ]
];