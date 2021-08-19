<?php


namespace Sammycorgi\LaravelHierarchy\Exceptions;


use Exception;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

class HierarchyException extends Exception
{
    /**
     * Throw an exception because the parent_id was set to null when Account with this already exists
     *
     * @param HasHierarchy $model
     * @throws HierarchyException
     */
    public static function throwRootNodeAlreadyExistsException(HasHierarchy $model)
    {
        $class = get_class($model);

        throw new static("Attempted to create a new top-level $class with ID {$model->getId()} when one already exists!");
    }

    /**
     * Throw an exception because the number of root nodes does not equal 1
     *
     * @param int $count
     * @throws HierarchyException
     */
    public static function throwInvalidRootCountException(int $count)
    {
        throw new static("Invalid number of root nodes found. This should be exactly 1. $count found");
    }
}
