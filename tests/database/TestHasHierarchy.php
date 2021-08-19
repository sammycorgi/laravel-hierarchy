<?php


namespace Tests\database;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

class TestHasHierarchy extends Model implements HasHierarchy
{
    use \Sammycorgi\LaravelHierarchy\Traits\HasHierarchy, HasFactory;

    protected $table = "test_has_hierarchy";
    public $timestamps = false;
    protected $guarded = [];

    /**
     * @inheritDoc
     */
    protected static function newFactory()
    {
        return TestHasHierarchyFactory::new();
    }
}