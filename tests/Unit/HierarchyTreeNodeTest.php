<?php


use Illuminate\Support\Collection;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeBuilder;
use Sammycorgi\LaravelHierarchy\Tree\HierarchyTreeNode;
use Tests\TestCase;
use Tests\Utility\MakesTreeNodes;
use Tests\Utility\MockHasHierarchy;

class HierarchyTreeNodeTest extends TestCase
{
    use MakesTreeNodes;

    private function makeTree($nodes)
    {
        return (new HierarchyTreeBuilder())->build($nodes);
    }

    public function test_it_can_be_determined_if_a_node_is_an_immediate_child_of_another_node()
    {
        $parent = new HierarchyTreeNode(new MockHasHierarchy(0, null));
        $child = new HierarchyTreeNode(new MockHasHierarchy(1, 0));

        $otherParent = new HierarchyTreeNode(new MockHasHierarchy(5, 5));

        $parent->setImmediateChildren(Collection::wrap($child));

        $this->assertTrue($child->isImmediateChildOf($parent));
        $this->assertFalse($child->isImmediateChildOf($otherParent));
    }

    public function test_child_nodes_are_assigned_a_parent_if_they_are_set_as_immediate_children_on_the_parent()
    {
        $parentId = 0;

        $children = 5;
        $childIds = range(1, $children);
        $parentIds = array_fill(0, $children, $parentId);

        $childNodes = $this->getNodes($childIds, $parentIds)->map(function(MockHasHierarchy $item) {
            return new HierarchyTreeNode($item);
        });

        $parent = new HierarchyTreeNode(new MockHasHierarchy($parentId, null));
        $parent->setImmediateChildren($childNodes);

        foreach ($childNodes as $child) {
            $this->assertTrue($child->isImmediateChildOf($parent));
        }
    }

    public function test_it_can_be_determined_if_a_node_is_a_parent_of_another_node()
    {
        $parent = new HierarchyTreeNode(new MockHasHierarchy(0, null));
        $child = new HierarchyTreeNode(new MockHasHierarchy(1, 0));
        $otherChild = new HierarchyTreeNode(new MockHasHierarchy(5, 5));

        $parent->setImmediateChildren(Collection::wrap($child));

        $this->assertTrue($parent->isImmediateParentOf($child));
        $this->assertFalse($parent->isImmediateParentOf($otherChild));
    }

    public function test_it_can_be_determined_if_a_node_is_a_parent_of_another_node_at_any_depth()
    {
        $parent = new HierarchyTreeNode(new MockHasHierarchy(0, null));
        $child = new HierarchyTreeNode(new MockHasHierarchy(1, 0));
        $deepChild = new HierarchyTreeNode(new MockHasHierarchy(2, 1));

        $parent->setImmediateChildren(Collection::wrap($child));
        $child->setImmediateChildren(Collection::wrap($deepChild));

        $this->assertTrue($parent->isAnyDepthParentOf($deepChild));
    }

    public function test_it_can_be_determined_if_a_node_is_a_child_of_another_node_at_any_depth()
    {
        $parent = new HierarchyTreeNode(new MockHasHierarchy(0, null));
        $child = new HierarchyTreeNode(new MockHasHierarchy(1, 0));
        $deepChild = new HierarchyTreeNode(new MockHasHierarchy(2, 1));

        $parent->setImmediateChildren(Collection::wrap($child));
        $child->setImmediateChildren(Collection::wrap($deepChild));

        $this->assertTrue($deepChild->isAnyDepthChildOf($parent));
    }

    public function test_child_nodes_are_assigned_correctly()
    {
        $ids = range(0, 9);

        //assign 3 nodes to root, 3 nodes to root first child, 3 nodes to root first child's first child
        $parents = [null, 0, 0, 0, 1, 1, 1, 4, 4, 4];
        $nodes = $this->getNodes($ids, $parents);
        $tree = $this->makeTree($nodes);

        $this->assertSame(3, sizeof($tree->getImmediateChildren()));
        $this->assertSame(3, sizeof($tree->getImmediateChildren()[0]->getImmediateChildren()));
        $this->assertSame(3, sizeof($tree->getImmediateChildren()[0]->getImmediateChildren()[0]->getImmediateChildren()));
    }

    public function test_all_children_for_a_node_can_be_found()
    {
        $count = 100;
        $ids = range(0, $count - 1);
        $parents = array_merge([null], range(0, $count - 2));
        $nodes = $this->getNodes($ids, $parents);
        $tree = $this->makeTree($nodes);

        for($i = 1; $i < $count; $i++) {
            $this->assertSame($count - $i, sizeof($tree->getAllChildren()));

            $tree = $tree->getImmediateChildren()->first();
        }
    }
}