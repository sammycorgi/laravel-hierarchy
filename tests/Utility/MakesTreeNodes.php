<?php


namespace Tests\Utility;


use Illuminate\Support\Collection;

trait MakesTreeNodes
{
    /**
     * @param array $ids
     * @param array $parents
     * @return array
     */
    protected function getNodes(array $ids, array $parents) : Collection
    {
        $nodes = [];

        for($i = 0; $i < sizeof($ids); $i++) {
            $nodes[$i] = new MockHasHierarchy($ids[$i], $parents[$i]);
        }

        return Collection::wrap($nodes);
    }
}