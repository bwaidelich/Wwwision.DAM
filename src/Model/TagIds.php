<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<TagId>
 */
final class TagIds implements IteratorAggregate, Countable
{

    /**
     * @param TagId[] $ids
     */
    private function __construct(
        private readonly array $ids,
    ) {}

    public static function create(TagId ...$ids): self
    {
        return new self($ids);
    }

    public static function fromArray(array $ids): self
    {
        return new self(
            array_map(static fn (TagId|string $id) => is_string($id) ? TagId::fromString($id) : $id, $ids)
        );
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->ids);
    }

    public function count(): int
    {
        return count($this->ids);
    }

    public function empty(): bool
    {
        return $this->ids === [];
    }

    /**
     * @return string[]
     */
    public function toStrings(): array
    {
        return array_map(static fn (TagId $id) => $id->value, $this->ids);
    }
}
