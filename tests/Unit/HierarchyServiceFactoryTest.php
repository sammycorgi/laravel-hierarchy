<?php

namespace Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;
use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Sammycorgi\LaravelHierarchy\Service\HierarchyService;
use Sammycorgi\LaravelHierarchy\Service\HierarchyServiceFactory;
use Tests\database\TestHasHierarchy;
use Tests\TestCase;

class HierarchyServiceFactoryTest extends TestCase
{
    /**
     * @param string|null $type
     * @param string $key
     */
    private function setConfigForDefaultPersister(?string $type = null, string $key = "key"): void
    {
        if ($type === null) {
            $type = get_class($this->mock(PersistsHierarchyChildren::class));
        }

        config(['hierarchy.persisters.default' => $key]);
        config(['hierarchy.persisters.' . $key => $type]);
    }

    private function setConfigForDefaultModelForInstanceGetter(): void
    {
        config(['hierarchy.eloquent.default_model' => get_class($this->mock(Model::class))]);
    }

    public function test_the_factory_will_return_a_service_with_the_driver_if_the_first_argument_implements_GetsHierarchyInstances()
    {
        $driverOrClass = $this->mock(GetsHierarchyInstances::class);

        $factory = app()->make(HierarchyServiceFactory::class);
        $this->setConfigForDefaultPersister();

        $this->assertSame($factory->for($driverOrClass)->getHierarchyInstanceGetter(), $driverOrClass);
    }

    public function test_the_factory_will_return_a_service_with_the_driver_and_args_if_the_first_argument_is_a_class_name_that_implements_GetsHierarchyInstances()
    {
        $driverOrClass = $this->mock(GetsHierarchyInstances::class);

        $factory = app()->make(HierarchyServiceFactory::class);
        $this->setConfigForDefaultPersister();

        $this->assertInstanceOf(get_class($driverOrClass), $factory->for(get_class($driverOrClass))->getHierarchyInstanceGetter());
    }

    public function test_the_factory_will_return_the_eloquent_getter_in_the_service_if_the_passed_driver_or_class_is_a_model()
    {
        $factory = app()->make(HierarchyServiceFactory::class);
        $this->setConfigForDefaultPersister();

        $this->assertInstanceOf(EloquentHierarchyInstanceGetter::class, $factory->for(TestHasHierarchy::class)->getHierarchyInstanceGetter());
    }

    public function test_the_factory_will_resolve_the_getter_from_the_config_if_it_is_set()
    {
        $driverOrClass = "something in the config";
        $impl = $this->mock(GetsHierarchyInstances::class);

        config(['hierarchy.instance_getters.' . $driverOrClass => get_class($impl)]);

        $factory = app()->make(HierarchyServiceFactory::class);
        $this->setConfigForDefaultPersister();

        $this->assertInstanceOf(get_class($impl), $factory->for($driverOrClass)->getHierarchyInstanceGetter());
    }

    public function test_an_exception_will_be_thrown_of_the_getter_could_not_be_found_in_the_config()
    {
        $driverOrClass = "something not in the config";

        $factory = app()->make(HierarchyServiceFactory::class);
        $this->setConfigForDefaultPersister();

        $this->expectException(BindingResolutionException::class);

        $factory->for($driverOrClass)->getHierarchyInstanceGetter();
    }

    public function test_the_factory_can_return_a_collection_of_ids_for_the_given_model()
    {
        $factory = app()->make(HierarchyServiceFactory::class);

        $model = $this->mock(Model::class);

        $id = "some model id";

        $model->shouldReceive('getId')->once()->andReturn($id);

        $ids = Collection::wrap([1, 2, 3, 4, 5, 8]);

        $this->setConfigForDefaultPersister();
        $this->setConfigForDefaultModelForInstanceGetter();

        $service = $this->mock(HierarchyService::class);

        $this->app->singleton(HierarchyService::class, function () use ($service) {
            return $service;
        });

        $service->shouldReceive('getChildIdsForId')->once()->with($id)->andReturn($ids);

        $this->assertSame($factory->getChildIdsFor($model), $ids);
    }
}