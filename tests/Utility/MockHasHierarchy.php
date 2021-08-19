<?php


namespace Tests\Utility;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Sammycorgi\LaravelHierarchy\Contracts\HasHierarchy;

class MockHasHierarchy implements HasHierarchy
{
    private int $id;
    private ?int $parentId;

    /**
     * MockHasHierarchy constructor.
     * @param int $id
     * @param int | null $parentId
     */
    public function __construct(int $id, ?int $parentId)
    {
        $this->id = $id;
        $this->parentId = $parentId;
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): string|int|null
    {
        return $this->parentId;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string|int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getParentIdKeyName(): string
    {
        return "parent_id";
    }

    /**
     * @inheritDoc
     */
    public function getIdKeyName(): string
    {
        return "id";
    }

    /**
     * @inheritDoc
     */
    public function parent(): BelongsTo
    {
        return Mockery::mock(BelongsTo::class);
    }

    /**
     * @inheritDoc
     */
    public function immediateChildren(): HasMany
    {
        return Mockery::mock(HasMany::class);
    }
}