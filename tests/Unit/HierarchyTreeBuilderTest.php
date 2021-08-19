<?php


namespace Tests\Unit;


use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Exceptions\HierarchyException;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeBuilder;
use Tests\TestCase;
use Tests\Utility\MakesTreeNodes;
use Tests\Utility\MockHasHierarchy;

class HierarchyTreeBuilderTest extends TestCase
{
    use MakesTreeNodes;

    private function makeTree(array|Collection $nodes)
    {
        return (new HierarchyTreeBuilder())->build(Collection::wrap($nodes));
    }

    public function test_a_tree_can_be_generated_from_an_array_of_items()
    {
        $count = 100;

        $ids = range(0, $count - 1);

        //setting first node as root, second node as child of root
        $parents = [null, 0];

        for($i = 2; $i < $count; $i++) {
            //select parent from list of already generated parent ids
            $parents[$i] = floor(rand(0, $i - 1));
        }

        $nodes = $this->getNodes($ids, $parents);

        $this->assertGreaterThanOrEqual(1, sizeof($this->makeTree($nodes)->getImmediateChildren()));
    }

    public function test_a_tree_can_be_generated_from_a_collection_of_items()
    {
        $count = 100;

        $ids = range(0, $count - 1);

        //setting first node as root, second node as child of root
        $parents = [null, 0];

        for($i = 2; $i < $count; $i++) {
            //select parent from list of already generated parent ids
            $parents[$i] = floor(rand(0, $i - 1));
        }

        $nodes = collect($this->getNodes($ids, $parents));

        $tree = $this->makeTree($nodes);

        $this->assertGreaterThanOrEqual(1, sizeof($tree->getImmediateChildren()));
    }

    public function test_tree_and_collections_will_yield_the_same_results_if_their_contents_are_the_same()
    {
        $count = 100;

        $ids = range(0, $count - 1);

        //setting first node as root, second node as child of root
        $parents = [null, 0];

        for($i = 2; $i < $count; $i++) {
            //select parent from list of already generated parent ids
            $parents[$i] = floor(rand(0, $i - 1));
        }

        $nodes = $this->getNodes($ids, $parents);

        $tree = $this->makeTree($nodes);
        $tree2 = $this->makeTree(collect($nodes));

        $this->assertSame(sizeof($tree2->getImmediateChildren()), sizeof($tree->getImmediateChildren()));
    }

    public function test_an_exception_is_thrown_if_there_is_no_root_node()
    {
        $child = new MockHasHierarchy(0, 1);

        $this->expectException(HierarchyException::class);

        $this->makeTree([$child]);
    }

    public function test_an_exception_is_thrown_if_there_are_too_many_root_nodes()
    {
        $root1 = new MockHasHierarchy(0, null);
        $root2 = new MockHasHierarchy(1, null);

        $this->expectException(HierarchyException::class);

        $this->makeTree([$root1, $root2]);
    }
}