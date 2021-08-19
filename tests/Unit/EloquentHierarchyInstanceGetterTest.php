<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\InstanceGetter\EloquentHierarchyInstanceGetter;
use Sammycorgi\LaravelHierarchy\Persist\CachePersister;
use Sammycorgi\LaravelHierarchy\Traits\HasHierarchy;
use Tests\database\TestHasHierarchy;
use Tests\TestCase;

class EloquentHierarchyInstanceGetterTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('hierarchy.persisters.default', 'a');
        $app['config']->set('hierarchy.persisters.a', CachePersister::class);

        $app['migrator']->path(__DIR__ . "/../database/migrations");
    }

    public function test_the_getter_will_call_the_count_method_to_return_the_count()
    {
        $count = 5;

        TestHasHierarchy::factory()->create();
        TestHasHierarchy::factory()->count($count)->create();

        $service = new EloquentHierarchyInstanceGetter(TestHasHierarchy::class);

        $this->assertSame($count + 1, $service->count());
    }

    public function test_the_getter_will_fetch_ids_and_parent_ids_from_the_models_to_get_all_records()
    {
        $models = new Collection;

        $models = $models->push(TestHasHierarchy::factory()->create());
        $models = $models->merge(TestHasHierarchy::factory()->count(5)->create());

        $service = new EloquentHierarchyInstanceGetter(TestHasHierarchy::class);

        $this->assertEquals($models->pluck('id'), $service->getAll()->pluck('id'));
    }

    public function test_the_getter_will_determine_if_an_id_has_any_children_by_checking_if_the_models_parent_id_field_exists_on_any_other_records()
    {
        $root = TestHasHierarchy::factory()->create();
        $child1 = TestHasHierarchy::factory()->create(['parent_id' => $root->id]);
        $child2 = TestHasHierarchy::factory()->create(['parent_id' => $child1->id]);

        $service = new EloquentHierarchyInstanceGetter(TestHasHierarchy::class);

        $this->assertTrue($service->hasChildren($root->id));
        $this->assertTrue($service->hasChildren($child1->id));
        $this->assertFalse($service->hasChildren($child2->id));
    }
}