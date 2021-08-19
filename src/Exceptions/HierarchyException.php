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
     * @throws HierarchyException
     */
    public static function throwInvalidRootCountException()
    {
        throw new static("Attempted to create new root node when one already exists!");
    }
}
