<?php


use Illuminate\Foundation\Testing\RefreshDatabase;
use Sammycorgi\LaravelHierarchy\Exceptions\HierarchyException;
use Sammycorgi\LaravelHierarchy\Persist\CachePersister;
use Tests\database\TestHasHierarchy;
use Tests\TestCase;

class HasHierarchyModelTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('hierarchy.persisters.default', 'a');
        $app['config']->set('hierarchy.persisters.a', CachePersister::class);

        $app['migrator']->path(__DIR__ . "/../database/migrations");
    }

    public function test_a_models_parent_can_be_retrieved()
    {
        $parent = TestHasHierarchy::factory()->create();
        $model = TestHasHierarchy::factory()->create(['parent_id' => $parent->id]);

        $this->assertTrue($model->parent->is($parent));
    }

    public function test_a_models_immediate_children_can_be_retrieved()
    {
        $parent = TestHasHierarchy::factory()->create();
        $children = TestHasHierarchy::factory()->count(10)->create(['parent_id' => $parent->id]);

        $this->assertEquals($parent->immediateChildren->pluck('id'), $children->pluck('id'));
    }

    public function test_an_exception_is_thrown_if_creating_multiple_root_nodes()
    {
        TestHasHierarchy::factory()->create();

        $this->expectException(HierarchyException::class);

        TestHasHierarchy::factory()->create();
    }

    public function test_newly_created_models_are_added_to_their_parents_hierarchy_child_id_keys()
    {
        $persister = $this->mock(CachePersister::class);

        $persister->shouldReceive('has')->andReturnFalse();
        $persister->shouldReceive('putIfNew');

        $parent = TestHasHierarchy::factory()->create();

        $persister->shouldReceive('insertOne')->with(TestHasHierarchy::class, $parent->id, 2);

        $child = TestHasHierarchy::factory()->create(['parent_id' => $parent->id]);

        $persister->shouldReceive('insertOne')->with(TestHasHierarchy::class, $child->id, 3);
        $persister->shouldReceive('insertOne')->with(TestHasHierarchy::class, $parent->id, 3);

        TestHasHierarchy::factory()->create(['parent_id' => $child->id]);
    }

    public function test_deleted_models_are_deleted_from_their_parents_hierarchy_child_id_keys()
    {
        $persister = $this->mock(CachePersister::class);

        $persister->shouldReceive('has')->andReturnFalse();
        $persister->shouldReceive('putIfNew');
        $persister->shouldReceive('insertOne');

        $parent = TestHasHierarchy::factory()->create();
        $child = TestHasHierarchy::factory()->create(['parent_id' => $parent->id]);
        $child2 = TestHasHierarchy::factory()->create(['parent_id' => $child->id]);

        $persister->shouldReceive('deleteOne')->once()->with(TestHasHierarchy::class, $parent->id, $child2->id);
        $persister->shouldReceive('deleteOne')->once()->with(TestHasHierarchy::class, $child->id, $child2->id);

        $child2->delete();
    }

    public function test_models_that_have_their_parent_id_updated_are_deleted_from_their_old_parent_hierarchy_and_added_to_the_new_one()
    {
        $persister = $this->mock(CachePersister::class);

        $persister->shouldReceive('has')->andReturnFalse();
        $persister->shouldReceive('putIfNew');

        $parent = TestHasHierarchy::factory()->create(); //id 1

        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $parent->id, 2);

        $branch1 = TestHasHierarchy::factory()->create(['parent_id' => $parent->id]); //id 2

        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $branch1->id, 3);
        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $parent->id, 3);

        $child = TestHasHierarchy::factory()->create(['parent_id' => $branch1->id]); //id 3

        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $parent->id, 4);

        $branch2 = TestHasHierarchy::factory()->create(['parent_id' => $parent->id]); //id 4

        $persister->shouldReceive('deleteOne')->once()->with(TestHasHierarchy::class, $parent->id, $child->id);
        $persister->shouldReceive('deleteOne')->once()->with(TestHasHierarchy::class, $branch1->id, $child->id);

        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $parent->id, $child->id);
        $persister->shouldReceive('insertOne')->once()->with(TestHasHierarchy::class, $branch2->id, $child->id);

        $child->update(['parent_id' => $branch2->id]);
    }
}