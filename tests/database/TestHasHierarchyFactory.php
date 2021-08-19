<?php


namespace Tests\database;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestHasHierarchyFactory extends Factory
{
    protected $model = TestHasHierarchy::class;

    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'parent_id' => optional(TestHasHierarchy::first())->id
        ];
    }
}