<?php


namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Models\HierarchyModelChildRecord;
use Sammycorgi\LaravelHierarchy\Persist\EloquentPersister;
use Tests\TestCase;

class EloquentPersisterTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['migrator']->path(__DIR__ . "/../../src/database/migrations");
    }

    public function test_the_persister_can_return_a_collection_of_child_ids_for_the_given_parent_id()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;
        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        HierarchyModelChildRecord::insert($ids->map(function ($id) use ($parentId, $type) {
            return [
                'type' => $type,
                'parent_id' => $parentId,
                'child_id' => $id
            ];
        })->toArray());

        $this->assertEquals($persister->get($type, $parentId), $ids);
    }

    public function test_the_persister_can_determine_if_there_are_any_child_records_for_the_given_id()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;

        HierarchyModelChildRecord::insert([
            'type' => $type,
            'parent_id' => $parentId,
            'child_id' => 2
        ]);

        $this->assertTrue($persister->has($type, $parentId));
        $this->assertFalse($persister->has($type, 2));
    }

    public function test_the_persister_can_create_a_new_record_and_return_the_updated_collection()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;
        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $newId = 11;

        HierarchyModelChildRecord::insert($ids->map(function ($id) use ($parentId, $type) {
            return [
                'type' => $type,
                'parent_id' => $parentId,
                'child_id' => $id
            ];
        })->toArray());

        $this->assertEquals($persister->insertOne($type, $parentId, $newId), $ids->push($newId));
    }

    public function test_the_persister_can_delete_a_record_and_return_the_updated_collection()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;

        $deletedId = 10;

        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, $deletedId]);

        HierarchyModelChildRecord::insert($ids->map(function ($id) use ($parentId, $type) {
            return [
                'type' => $type,
                'parent_id' => $parentId,
                'child_id' => $id
            ];
        })->toArray());

        $this->assertEquals($persister->deleteOne($type, $parentId, $deletedId), $ids->forget($ids->search($deletedId)));
    }

    public function test_the_persister_can_create_new_child_records_given_a_collection_of_ids()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;

        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $this->assertTrue($persister->put($type, $parentId, $ids));

        $this->assertEquals(HierarchyModelChildRecord::pluck('child_id'), $ids);
    }

    public function test_the_persister_can_add_new_child_records_if_they_dont_already_exist_and_return_them()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;

        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $closure = function() use ($ids) { return $ids; };

        $this->assertEquals($persister->putIfNew($type, $parentId, $closure), $ids);
        $this->assertEquals($persister->putIfNew($type, $parentId, $closure), $ids);
        $this->assertEquals($persister->putIfNew($type, $parentId, $closure), $ids);

        $this->assertSame(HierarchyModelChildRecord::count(), $ids->count());
    }

    public function test_the_persister_can_delete_all_child_ids_for_a_given_parent_id()
    {
        $persister = new EloquentPersister;

        $type = "some type";
        $parentId = 1;
        $ids = Collection::wrap([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        HierarchyModelChildRecord::insert($ids->map(function ($id) use ($parentId, $type) {
            return [
                'type' => $type,
                'parent_id' => $parentId,
                'child_id' => $id
            ];
        })->toArray());

        $this->assertTrue($persister->delete($type, $parentId));

        $this->assertSame(HierarchyModelChildRecord::count(), 0);
    }
}