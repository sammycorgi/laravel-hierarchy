<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Tests\TestCase;

class EloquentHierarchyInstanceGetterTest extends TestCase
{
    public function test_the_getter_will_call_the_count_method_to_return_the_count()
    {
        $class = $this->mock(Model::class);

        $count = 5;

        $class->shouldReceive('count')->once()->andReturn($count);

        $service = new EloquentHierarchyInstanceGetter(get_class($class));

        $this->assertSame($count, $service->count());
    }

    public function test_the_getter_will_fetch_ids_and_parent_ids_from_the_models_to_get_all_records()
    {
        $class = $this->mock(Model::class);

        $parentIdKeyName = "a parent id key";
        $idKeyName = "an id key";

        $models = new Collection;
        $query = $this->mock(Builder::class);
        $query->shouldReceive('get')->once()->andReturn($models);

        $class->shouldReceive('select')->once()->with([$idKeyName, $parentIdKeyName])->andReturn($query);

        $service = new EloquentHierarchyInstanceGetter(get_class($class));

        $this->assertSame($models, $service->getAll());
    }

    public function test_the_getter_will_determine_if_an_id_has_any_children_by_checking_if_the_models_parent_id_field_is()
    {
        $class = $this->mock(Model::class);

        $parentIdKeyName = "a parent id key";
        $id = "an id";

        foreach([0, 1] as $count) {
            $query = $this->mock(Builder::class);
            $query->shouldReceive('count')->once()->andReturn($count);

            $class->shouldReceive('where')->once()->with($parentIdKeyName, $id)->andReturn($query);

            $service = new EloquentHierarchyInstanceGetter(get_class($class));

            $this->assertTrue($service->hasChildren($id));
        }
    }
}