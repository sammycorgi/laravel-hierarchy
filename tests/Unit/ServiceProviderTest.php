<?php

namespace Tests\Unit;

use Sammycorgi\LaravelHierarchy\HierarchyServiceProvider;
use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Sammycorgi\LaravelHierarchy\Traits\HasHierarchy;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [HierarchyServiceProvider::class];
    }

    public function test_the_eloquent_default_model_is_automatically_injected()
    {
        $model = $this->mock(HasHierarchy::class);

        config(['hierarchy.eloquent.default_model' => get_class($model)]);

        $service = app()->make(EloquentHierarchyInstanceGetter::class);

        $this->assertSame($service->getClassName(), get_class($model));
    }
}