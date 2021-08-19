<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Contracts\GetsHierarchyInstances;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;
use Sammycorgi\LaravelHierarchy\Contracts\PersistsHierarchyChildren;
use Sammycorgi\LaravelHierarchy\Service\HierarchyService;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeBuilder;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeNode;
use Tests\TestCase;

class HierarchyServiceTest extends TestCase
{
    public function test_the_service_can_return_a_tree_for_all_models_and_will_return_the_same_tree_without_rebuilding_it_for_subsequent_calls()
    {
        $builder = $this->mock(HierarchyTreeBuilder::class);
        $persister = $this->mock(PersistsHierarchyChildren::class);
        $instanceGetter = $this->mock(GetsHierarchyInstances::class);

        $service = new HierarchyService($builder, $persister, $instanceGetter);

        $calls = 10;

        $instanceGetter->shouldReceive('count')->times($calls)->andReturn(1);

        $models = new Collection;

        $tree = new HierarchyTreeNode($this->mock(HasHierarchy::class));

        $instanceGetter->shouldReceive('getAll')->once()->andReturn($models);
        $builder->shouldReceive('build')->once()->andReturn($tree);

        for($i = 0; $i < $calls; $i++) {
            $this->assertSame($service->getTreeForAll(), $tree);
        }
    }

    public function test_the_service_can_return_a_tree_for_all_models_and_will_rebuild_the_tree_if_the_number_of_models_has_changed()
    {
        $builder = $this->mock(HierarchyTreeBuilder::class);
        $persister = $this->mock(PersistsHierarchyChildren::class);
        $instanceGetter = $this->mock(GetsHierarchyInstances::class);

        $service = new HierarchyService($builder, $persister, $instanceGetter);

        $instanceGetter->shouldReceive('count')->andReturn(1, 2);

        $models = new Collection;

        $tree1 = new HierarchyTreeNode($this->mock(HasHierarchy::class));
        $tree2 = new HierarchyTreeNode($this->mock(HasHierarchy::class));

        $instanceGetter->shouldReceive('getAll')->times(2)->andReturn($models);
        $builder->shouldReceive('build')->times(2)->andReturn($tree1, $tree2);

        $this->assertSame($service->getTreeForAll(), $tree1);
        $this->assertSame($service->getTreeForAll(), $tree2);
        $this->assertSame($service->getTreeForAll(), $tree2);
        $this->assertNotSame($service->getTreeForAll(), $tree1);
    }
}